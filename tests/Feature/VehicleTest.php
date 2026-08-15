<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class VehicleTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_can_create_vehicle_for_itself(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);

        $response = $this->actingAs($mitra)->post(route('vehicles.store'), [
            'nama' => 'Mobil Test',
            'plat_nomor' => 'B 1234 XYZ',
            'jenis' => 'sedan',
            'kapasitas_penumpang' => 7,
            'prioritas_travel' => 2,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 300000, 'harga_sewa_dengan_sopir_per_hari' => 300000,
            'foto' => UploadedFile::fake()->image('vehicle.jpg'),
        ]);

        $response->assertRedirect(route('vehicles.index'));
        $this->assertDatabaseHas('vehicles', [
            'mitra_id' => $mitra->id,
            'nama' => 'Mobil Test',
            'kapasitas_penumpang' => 7,
            'prioritas_travel' => 2,
        ]);
    }

    public function test_admin_can_approve_vehicle(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Mobil Pending',
            'plat_nomor' => 'B 9999 ABC',
            'jenis' => 'suv',
            'status' => 'maintenance',
            'harga_sewa_tanpa_sopir_per_hari' => 450000, 'harga_sewa_dengan_sopir_per_hari' => 450000,
            'is_approved' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.vehicles.approve', $vehicle));

        $response->assertRedirect(route('admin.vehicles.index'));
        $this->assertDatabaseHas('vehicles', [
            'id' => $vehicle->id,
            'is_approved' => true,
        ]);
    }

    public function test_mitra_can_reorder_only_its_own_vehicles(): void
    {
        $mitra = User::factory()->create(['role' => 'mitra']);
        $otherMitra = User::factory()->create(['role' => 'mitra']);
        $firstVehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Mobil Satu',
            'plat_nomor' => 'B 1111 AAA',
            'jenis' => 'MPV',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $secondVehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Mobil Dua',
            'plat_nomor' => 'B 2222 BBB',
            'jenis' => 'MPV',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $otherVehicle = Vehicle::create([
            'mitra_id' => $otherMitra->id,
            'nama' => 'Mobil Mitra Lain',
            'plat_nomor' => 'B 3333 CCC',
            'jenis' => 'MPV',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($mitra)->post(route('vehicles.reorder'), [
            'vehicle_ids' => [$secondVehicle->id, $firstVehicle->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vehicles', ['id' => $secondVehicle->id, 'prioritas_travel' => 1]);
        $this->assertDatabaseHas('vehicles', ['id' => $firstVehicle->id, 'prioritas_travel' => 2]);
        $this->assertDatabaseHas('vehicles', ['id' => $otherVehicle->id, 'prioritas_travel' => 0]);
    }
}
