<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class DriverManagementController extends Controller
{
    public function index()
    {
        $drivers = User::where('role', 'driver')
            ->latest()
            ->get();

        $stats = [
            'total' => $drivers->count(),
            'approved' => $drivers->count(),
            'pending' => 0,
        ];

        return view('admin.drivers.index', compact('drivers', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'driver',
        ]);

        return redirect()->route('admin.drivers.index')->with('success', 'Akun sopir berhasil dibuat.');
    }
}
