<?php

namespace App\Http\Controllers;

use App\Models\RevenueShare;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class RevenueShareController extends Controller
{
    public function index()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $revenueShares = RevenueShare::with('mitra')->latest()->get();
        $mitras = User::where('role', 'mitra')->orderBy('name')->get();
        $globalShare = RevenueShare::whereNull('mitra_id')->latest()->first();

        $stats = [
            'total' => $revenueShares->count(),
            'global' => $revenueShares->whereNull('mitra_id')->count(),
            'specific' => $revenueShares->whereNotNull('mitra_id')->count(),
            'active_platform' => $globalShare?->persen_platform ?? 0,
            'active_mitra' => $globalShare?->persen_mitra ?? 0,
        ];

        return view('revenue-shares.index', compact('revenueShares', 'mitras', 'globalShare', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'mitra_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'mitra'))],
            'persen_platform' => ['required', 'numeric', 'between:0,100'],
            'persen_mitra' => ['required', 'numeric', 'between:0,100'],
        ]);

        if (round((float) $validated['persen_platform'] + (float) $validated['persen_mitra'], 2) !== 100.00) {
            return back()
                ->withErrors(['persen_mitra' => 'Total persentase platform dan mitra harus 100%.'])
                ->withInput();
        }

        RevenueShare::create($validated);

        return redirect()->route('admin.revenue-shares.index')->with('success', 'Revenue share berhasil disimpan.');
    }

    public function edit(RevenueShare $revenueShare)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $mitras = User::where('role', 'mitra')->orderBy('name')->get();

        return view('revenue-shares.edit', compact('revenueShare', 'mitras'));
    }

    public function update(Request $request, RevenueShare $revenueShare): RedirectResponse
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'mitra_id' => ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'mitra'))],
            'persen_platform' => ['required', 'numeric', 'between:0,100'],
            'persen_mitra' => ['required', 'numeric', 'between:0,100'],
        ]);

        if (round((float) $validated['persen_platform'] + (float) $validated['persen_mitra'], 2) !== 100.00) {
            return back()
                ->withErrors(['persen_mitra' => 'Total persentase platform dan mitra harus 100%.'])
                ->withInput();
        }

        $revenueShare->update($validated);

        return redirect()->route('admin.revenue-shares.index')->with('success', 'Revenue share berhasil diperbarui.');
    }

    public function destroy(RevenueShare $revenueShare): RedirectResponse
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $revenueShare->delete();

        return redirect()->route('admin.revenue-shares.index')->with('success', 'Revenue share berhasil dihapus.');
    }
}
