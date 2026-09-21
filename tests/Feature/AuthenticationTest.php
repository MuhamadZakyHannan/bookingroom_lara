<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_user_can_log_in_and_open_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'Password1!',
        ]);

        $this->post(route('login.store', absolute: false), [
            'email' => $user->email,
            'password' => 'Password1!',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->get(route('dashboard', absolute: false))->assertOk()->assertSee('Dashboard Ruangan');
    }

    public function test_registration_requires_a_strong_password(): void
    {
        $this->post(route('register.store', absolute: false), [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.com',
            'password' => 'lemah',
            'password_confirmation' => 'lemah',
        ])->assertSessionHasErrors('password');

        $this->assertDatabaseMissing('users', ['email' => 'baru@example.com']);
    }

    public function test_user_can_register_with_a_strong_password(): void
    {
        $this->post(route('register.store', absolute: false), [
            'name' => 'Pengguna Baru',
            'email' => 'baru@example.com',
            'password' => 'Kuat#2026',
            'password_confirmation' => 'Kuat#2026',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'email' => 'baru@example.com',
            'role' => 'user',
        ]);
    }
}
