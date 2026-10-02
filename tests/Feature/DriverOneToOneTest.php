<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Aturan "1 sopir 1 mobil": satu sopir hanya boleh terpasang di satu unit.
 * Saat sopir dipindah ke unit lain, unit lamanya otomatis dikosongkan.
 */
class DriverOneToOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigning_driver_to_second_vehicle_frees_the_first_one(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $driver = User::factory()->create(['role' => 'driver']);

        $unitA = $this->vehicle($mitra, 'EB 1001 AA');
        $unitB = $this->vehicle($mitra, 'EB 1002 BB');

        // pasang sopir ke unit A
        $this->actingAs($admin)
            ->post(route('admin.vehicles.assign-driver', $unitA), ['sopir_id' => $driver->id])
            ->assertRedirect();

        $this->assertSame($driver->id, $unitA->fresh()->sopir_id);

        // pindah sopir ke unit B -> unit A harus kosong
        $this->actingAs($admin)
            ->post(route('admin.vehicles.assign-driver', $unitB), ['sopir_id' => $driver->id])
            ->assertRedirect();

        $unitA->refresh();
        $unitB->refresh();

        $this->assertSame($driver->id, $unitB->sopir_id);
        $this->assertNull($unitA->sopir_id, 'Unit lama harus dikosongkan supaya 1 sopir = 1 mobil.');
        $this->assertSame(1, Vehicle::where('sopir_id', $driver->id)->count());
    }

    public function test_driver_can_be_detached_from_vehicle(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $driver = User::factory()->create(['role' => 'driver']);

        $unit = $this->vehicle($mitra, 'EB 1003 CC', ['sopir_id' => $driver->id]);

        $this->actingAs($admin)
            ->post(route('admin.vehicles.assign-driver', $unit), ['sopir_id' => ''])
            ->assertRedirect();

        $this->assertNull($unit->fresh()->sopir_id);
    }

    private function vehicle(User $mitra, string $plat, array $attributes = []): Vehicle
    {
        return Vehicle::create(array_merge([
            'mitra_id' => $mitra->id,
            'nama' => 'Toyota Avanza',
            'plat_nomor' => $plat,
            'jenis' => 'mpv',
            'warna' => 'Putih',
            'layanan' => 'rental',
            'kapasitas_penumpang' => 7,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 400000,
            'harga_sewa_dengan_sopir_per_hari' => 500000,
            'tarif_sopir_harian' => 150000,
            'is_approved' => true,
        ], $attributes));
    }
}
