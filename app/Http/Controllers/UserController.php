<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $search = trim((string) $request->query('q', ''));
        $role = $request->query('role');
        $role = in_array($role, ['admin', 'customer', 'driver', 'mitra'], true) ? $role : null;

        $users = User::query()
            ->when($search !== '', fn ($query) => $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($role, fn ($query) => $query->where('role', $role))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    public function toggleActive(User $user)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($user->id === Auth::id()) {
            return back()->withErrors(['user' => 'Anda tidak dapat menonaktifkan akun sendiri.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        if (! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        AuditLog::record(
            $user->is_active ? 'activate_user' : 'deactivate_user',
            ($user->is_active ? 'Mengaktifkan' : 'Menonaktifkan').' user '.$user->name.' ('.$user->email.')',
            User::class,
            $user->id
        );

        return back()->with('success', 'Status user '.$user->name.' berhasil diperbarui.');
    }

    public function resetPassword(Request $request, User $user)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => Hash::make($validated['password'])]);

        AuditLog::record('reset_password', 'Mereset password user '.$user->name.' ('.$user->email.')', User::class, $user->id);

        return back()->with('success', 'Password '.$user->name.' berhasil direset.');
    }
}
