<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use App\Services\UserAdministrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        return view('admin.users.index', [
            'users' => User::query()
                ->withCount('bookings')
                ->when($search !== '', fn ($query) => $query->where(fn ($nested) => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
                ->orderByRaw("CASE WHEN role = 'admin' THEN 0 ELSE 1 END")
                ->orderBy('name')
                ->paginate(20)
                ->withQueryString(),
            'roles' => UserRole::cases(),
            'search' => $search,
        ]);
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user, UserAdministrationService $users): RedirectResponse
    {
        $users->updateRole($request->user(), $user, UserRole::from($request->validated('role')));

        return back()->with('success', 'Role pengguna berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user, UserAdministrationService $users): RedirectResponse
    {
        $users->delete($request->user(), $user);

        return back()->with('success', 'Pengguna tanpa riwayat berhasil dihapus.');
    }
}
