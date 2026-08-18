<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVehicleRequest;
use App\Http\Requests\UpdateVehicleRequest;
use App\Models\AuditLog;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    public function index()
    {
        if (Auth::user()->role === 'mitra') {
            $vehicles = Vehicle::with('mitra')
                ->where('mitra_id', Auth::id())
                ->orderByRaw('CASE WHEN prioritas_travel > 0 THEN 0 ELSE 1 END')
                ->orderBy('prioritas_travel')
                ->orderBy('created_at')
                ->get();

            return view('mitra.vehicles.index', compact('vehicles'));
        }

        $vehicles = Vehicle::with(['mitra', 'sopir'])->latest()->get();
        $mitras = User::where('role', 'mitra')->get();
        $drivers = User::where('role', 'driver')->get();
        $stats = [
            'total' => $vehicles->count(),
            'tersedia' => $vehicles->where('status', 'tersedia')->count(),
            'disewa' => $vehicles->where('status', 'disewa')->count(),
            'maintenance' => $vehicles->where('status', 'maintenance')->count(),
            'pendingApproval' => $vehicles->where('is_approved', false)->count(),
        ];

        return view('admin.vehicles.index', compact('vehicles', 'mitras', 'drivers', 'stats'));
    }

    public function create()
    {
        return view('mitra.vehicles.create');
    }

    public function store(StoreVehicleRequest $request)
    {
        $data = $request->only(['nama', 'plat_nomor', 'jenis', 'kapasitas_penumpang', 'prioritas_travel', 'status', 'harga_sewa_tanpa_sopir_per_hari', 'harga_sewa_dengan_sopir_per_hari']);
        $data['kapasitas_penumpang'] ??= 4;
        $data['prioritas_travel'] ??= 0;
        $data['tarif_sopir_harian'] = $request->integer('tarif_sopir_harian', 150000);
        $data['mitra_id'] = Auth::id();
        $data['is_approved'] = false;

        if ($request->hasFile('foto')) {
            $data['foto'] = $request->file('foto')->store('vehicles', 'public');
        }

        Vehicle::create($data);

        return redirect()->route('vehicles.index')->with('success', 'Unit kendaraan berhasil ditambahkan.');
    }

    public function edit(Vehicle $vehicle)
    {
        $this->authorize('update', $vehicle);

        return view('mitra.vehicles.edit', compact('vehicle'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle)
    {
        $fields = ['nama', 'plat_nomor', 'jenis', 'kapasitas_penumpang', 'status', 'harga_sewa_tanpa_sopir_per_hari', 'harga_sewa_dengan_sopir_per_hari', 'tarif_sopir_harian'];
        if (Auth::user()->role === 'mitra') {
            $fields[] = 'prioritas_travel';
        }

        $data = $request->only($fields);
        $data['kapasitas_penumpang'] ??= $vehicle->kapasitas_penumpang ?: 4;
        $data['tarif_sopir_harian'] ??= $vehicle->tarif_sopir_harian ?: 150000;
        if (Auth::user()->role === 'mitra') {
            $data['prioritas_travel'] ??= $vehicle->prioritas_travel ?: 0;
        }

        if ($request->hasFile('foto')) {
            if ($vehicle->foto) {
                Storage::disk('public')->delete($vehicle->foto);
            }
            $data['foto'] = $request->file('foto')->store('vehicles', 'public');
        }

        $vehicle->update($data);

        return redirect()->route('vehicles.index')->with('success', 'Unit kendaraan berhasil diperbarui.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $this->authorize('delete', $vehicle);

        if ($vehicle->foto) {
            Storage::disk('public')->delete($vehicle->foto);
        }

        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('success', 'Unit kendaraan berhasil dihapus.');
    }

    public function approve(Vehicle $vehicle)
    {
        $this->authorize('approve', $vehicle);

        $vehicle->update(['is_approved' => true]);

        AuditLog::record('approve_vehicle', 'Menyetujui kendaraan '.$vehicle->nama, Vehicle::class, $vehicle->id);

        return redirect()->route('admin.vehicles.index')->with('success', 'Unit kendaraan berhasil disetujui.');
    }

    public function assignDriver(Request $request, Vehicle $vehicle)
    {
        $this->authorize('assignDriver', $vehicle);

        $validated = $request->validate([
            'sopir_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'driver')),
            ],
        ]);

        $sopirId = $validated['sopir_id'] ?? null;

        if ($sopirId) {
            Vehicle::where('sopir_id', $sopirId)->update(['sopir_id' => null]);
        }

        $vehicle->update(['sopir_id' => $sopirId]);

        AuditLog::record('assign_driver_vehicle', 'Menugaskan sopir ke kendaraan '.$vehicle->nama, Vehicle::class, $vehicle->id);

        return back()->with('success', 'Sopir berhasil ditugaskan ke kendaraan.');
    }

    public function reorder(Request $request)
    {
        if (Auth::user()->role !== 'mitra') {
            abort(403);
        }

        $validated = $request->validate([
            'vehicle_ids' => ['required', 'array', 'min:1'],
            'vehicle_ids.*' => ['integer', 'distinct'],
        ]);

        $vehicleIds = array_map('intval', $validated['vehicle_ids']);
        $ownedVehicles = Vehicle::where('mitra_id', Auth::id())
            ->whereIn('id', $vehicleIds)
            ->get()
            ->keyBy('id');

        if ($ownedVehicles->count() !== count($vehicleIds)) {
            return back()->withErrors(['vehicle_ids' => 'Urutan kendaraan tidak valid. Muat ulang halaman lalu coba lagi.']);
        }

        DB::transaction(function () use ($vehicleIds, $ownedVehicles) {
            foreach ($vehicleIds as $priority => $vehicleId) {
                $ownedVehicles[$vehicleId]->update(['prioritas_travel' => $priority + 1]);
            }
        });

        return back()->with('success', 'Prioritas antrean travel berhasil diperbarui.');
    }
}
