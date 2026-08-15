<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\RouteAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RouteController extends Controller
{
    public function index()
    {
        $routes = Route::with(['assignments.mitra.vehicles'])->latest()->get();

        return view('admin.routes.index', compact('routes'));
    }

    public function create()
    {
        $mitras = User::where('role', 'mitra')->withCount('vehicles')->orderBy('name')->get();

        return view('admin.routes.create', compact('mitras'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'origin'            => ['required', 'string', 'max:255'],
            'destination'       => ['required', 'string', 'max:255'],
            'service_type'      => ['required', 'in:travel'],
            'price'             => ['required', 'integer', 'min:0'],
            'mitra_id_pagi'   => ['nullable', Rule::exists('users', 'id')->where('role', 'mitra')],
            'mitra_id_siang'  => ['nullable', Rule::exists('users', 'id')->where('role', 'mitra')],
        ]);

        $route = Route::create([
            'origin'       => $validated['origin'],
            'destination'  => $validated['destination'],
            'service_type' => $validated['service_type'],
            'price'        => $validated['price'],
        ]);

        $this->syncAssignments($route, 'pagi', $validated['mitra_id_pagi'] ?? null);
        $this->syncAssignments($route, 'siang', $validated['mitra_id_siang'] ?? null);

        return redirect()->route('admin.routes.index')->with('success', 'Rute berhasil ditambahkan.');
    }

    public function edit(Route $route)
    {
        $route->load('assignments');
        $mitras = User::where('role', 'mitra')->withCount('vehicles')->orderBy('name')->get();

        return view('admin.routes.edit', compact('route', 'mitras'));
    }

    public function update(Request $request, Route $route): RedirectResponse
    {
        $validated = $request->validate([
            'origin'            => ['required', 'string', 'max:255'],
            'destination'       => ['required', 'string', 'max:255'],
            'service_type'      => ['required', 'in:travel'],
            'price'             => ['required', 'integer', 'min:0'],
            'mitra_id_pagi'   => ['nullable', Rule::exists('users', 'id')->where('role', 'mitra')],
            'mitra_id_siang'  => ['nullable', Rule::exists('users', 'id')->where('role', 'mitra')],
        ]);

        $route->update([
            'origin'       => $validated['origin'],
            'destination'  => $validated['destination'],
            'service_type' => $validated['service_type'],
            'price'        => $validated['price'],
        ]);

        $this->syncAssignments($route, 'pagi', $validated['mitra_id_pagi'] ?? null);
        $this->syncAssignments($route, 'siang', $validated['mitra_id_siang'] ?? null);

        return redirect()->route('admin.routes.index')->with('success', 'Rute berhasil diperbarui.');
    }

    public function destroy(Route $route): RedirectResponse
    {
        $route->delete();

        return redirect()->route('admin.routes.index')->with('success', 'Rute berhasil dihapus.');
    }

    private function syncAssignments(Route $route, string $session, ?int $mitraId): void
    {
        RouteAssignment::where('route_id', $route->id)
            ->where('session', $session)
            ->delete();

        if ($mitraId) {
            RouteAssignment::create([
                'route_id' => $route->id,
                'session' => $session,
                'mitra_id' => $mitraId,
                'priority' => 1,
            ]);
        }
    }
}
