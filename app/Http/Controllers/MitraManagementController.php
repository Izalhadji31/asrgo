<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class MitraManagementController extends Controller
{
    public function index()
    {
        $mitras = User::where('role', 'mitra')
            ->with('vehicles.sopir')
            ->latest()
            ->get();

        $drivers = User::where('role', 'driver')->get();

        $assignedDriverIds = Vehicle::whereNotNull('sopir_id')->pluck('sopir_id')->toArray();

        return view('admin.mitras.index', compact('mitras', 'drivers', 'assignedDriverIds'));
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
            'role'     => 'mitra',
        ]);

        return redirect()->route('admin.mitras.index')->with('success', 'Akun mitra berhasil dibuat.');
    }
}
