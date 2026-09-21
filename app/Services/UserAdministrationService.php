<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserAdministrationService
{
    public function updateRole(User $actor, User $target, UserRole $role): User
    {
        return DB::transaction(function () use ($actor, $target, $role): User {
            $lockedTarget = User::query()->lockForUpdate()->findOrFail($target->id);

            if ($actor->is($lockedTarget)) {
                throw ValidationException::withMessages(['role' => 'Role akun yang sedang digunakan tidak dapat diubah sendiri.']);
            }

            $this->guardLastAdmin($lockedTarget, $role);
            $lockedTarget->update(['role' => $role->value]);

            return $lockedTarget->refresh();
        });
    }

    public function delete(User $actor, User $target): void
    {
        DB::transaction(function () use ($actor, $target): void {
            $lockedTarget = User::query()->lockForUpdate()->findOrFail($target->id);

            if ($actor->is($lockedTarget)) {
                throw ValidationException::withMessages(['user' => 'Akun yang sedang digunakan tidak dapat dihapus.']);
            }

            $this->guardLastAdmin($lockedTarget, UserRole::User);

            if ($lockedTarget->bookings()->exists()) {
                throw ValidationException::withMessages([
                    'user' => 'Pengguna memiliki riwayat booking dan tidak dapat dihapus. Ubah role bila diperlukan.',
                ]);
            }

            $lockedTarget->delete();
        });
    }

    private function guardLastAdmin(User $target, UserRole $newRole): void
    {
        if ($target->role === UserRole::Admin->value
            && $newRole !== UserRole::Admin
            && User::query()->where('role', UserRole::Admin->value)->lockForUpdate()->count() <= 1) {
            throw ValidationException::withMessages(['role' => 'Minimal satu akun administrator harus tetap tersedia.']);
        }
    }
}
