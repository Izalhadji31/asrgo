<?php

namespace Tests\Feature;

use Carbon\Carbon;
use App\Models\Booking;
use App\Models\Route;
use App\Models\RouteAssignment;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_overlapping_booking_for_same_vehicle_is_rejected(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Mobil A',
            'plat_nomor' => 'ABC 1111',
            'jenis' => 'mobil',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 150000, 'harga_sewa_dengan_sopir_per_hari' => 150000,
            'is_approved' => true,
        ]);

        Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2026-08-10',
            'tanggal_selesai' => '2026-08-12',
            'status' => 'confirmed',
            'total_harga' => 300000,
        ]);

        $response = $this->actingAs($customer)->post(route('bookings.store'), [
            'service_type' => 'rental',
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2026-08-11',
            'tanggal_selesai' => '2026-08-13',
            'with_driver' => '1',
            'duration' => '2',
        ]);

        $response->assertSessionHasErrors(['tanggal_mulai']);
    }

    public function test_customer_can_create_a_rental_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Mobil Rental Berhasil',
            'plat_nomor' => 'ABC 1001',
            'jenis' => 'MPV',
            'kapasitas_penumpang' => 6,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 200000, 'harga_sewa_dengan_sopir_per_hari' => 200000,
            'is_approved' => true,
        ]);
        $date = today()->addDay()->toDateString();

        $response = $this->actingAs($customer)->post(route('bookings.store'), [
            'service_type' => 'rental',
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => $date,
            'with_driver' => '0',
            'duration' => '2',
        ]);

        $booking = Booking::latest('id')->first();
        $response->assertRedirect(route('payments.show', $booking));
        $this->assertDatabaseHas('bookings', [
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'service_type' => 'rental',
            'total_harga' => 400000,
        ]);
        $this->assertSame($date, $booking->tanggal_mulai->toDateString());
        $this->assertSame(today()->addDays(2)->toDateString(), $booking->tanggal_selesai->toDateString());
    }

    public function test_driver_assignment_is_rejected_when_driver_has_conflict(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $driver = User::factory()->create(['role' => 'driver']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Mobil B',
            'plat_nomor' => 'ABC 2222',
            'jenis' => 'mobil',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 120000, 'harga_sewa_dengan_sopir_per_hari' => 120000,
            'is_approved' => true,
        ]);

        Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'sopir_id' => $driver->id,
            'tanggal_mulai' => '2026-09-01',
            'tanggal_selesai' => '2026-09-03',
            'status' => 'sopir_assigned',
            'total_harga' => 240000,
        ]);

        $targetBooking = Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2026-09-02',
            'tanggal_selesai' => '2026-09-04',
            'status' => 'pending',
            'total_harga' => 240000,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.bookings.assign-driver', $targetBooking), [
            'sopir_id' => $driver->id,
        ]);

        $response->assertSessionHasErrors(['sopir_id']);
    }

    public function test_admin_cannot_assign_a_non_driver_to_a_booking(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Mobil Validasi',
            'plat_nomor' => 'ABC 2233',
            'jenis' => 'mobil',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2027-01-01',
            'tanggal_selesai' => '2027-01-01',
            'status' => 'pending',
            'total_harga' => 100000,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.bookings.assign-driver', $booking), [
            'sopir_id' => $customer->id,
        ]);

        $response->assertSessionHasErrors(['sopir_id']);
        $this->assertDatabaseMissing('bookings', ['id' => $booking->id, 'sopir_id' => $customer->id]);
    }

    public function test_driver_cannot_complete_another_drivers_booking(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $assignedDriver = User::factory()->create(['role' => 'driver']);
        $otherDriver = User::factory()->create(['role' => 'driver']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Mobil Driver',
            'plat_nomor' => 'ABC 2244',
            'jenis' => 'mobil',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'sopir_id' => $assignedDriver->id,
            'tanggal_mulai' => today()->toDateString(),
            'tanggal_selesai' => today()->toDateString(),
            'status' => 'sopir_assigned',
            'total_harga' => 100000,
        ]);

        $response = $this->actingAs($otherDriver)->post(route('bookings.complete', $booking));

        $response->assertForbidden();
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'status' => 'sopir_assigned']);
    }

    public function test_travel_skips_a_vehicle_reserved_for_rental(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $driverOne = User::factory()->create(['role' => 'driver']);
        $driverTwo = User::factory()->create(['role' => 'driver']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $rentalVehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'sopir_id' => $driverOne->id,
            'nama' => 'Mobil Rental',
            'plat_nomor' => 'ABC 2255',
            'jenis' => 'MPV',
            'kapasitas_penumpang' => 4,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $travelVehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'sopir_id' => $driverTwo->id,
            'nama' => 'Mobil Travel',
            'plat_nomor' => 'ABC 2266',
            'jenis' => 'MPV',
            'kapasitas_penumpang' => 4,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $route = Route::create([
            'origin' => 'Kota E',
            'destination' => 'Kota F',
            'service_type' => 'travel',
            'price' => 150000,
        ]);
        RouteAssignment::create([
            'route_id' => $route->id,
            'mitra_id' => $mitra->id,
            'session' => 'pagi',
            'priority' => 1,
        ]);
        $date = today()->addDay()->toDateString();

        Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $rentalVehicle->id,
            'tanggal_mulai' => $date,
            'tanggal_selesai' => $date,
            'status' => 'pending',
            'total_harga' => 100000,
        ]);

        $response = $this->actingAs($customer)->post(route('bookings.store'), [
            'service_type' => 'travel',
            'route_id' => $route->id,
            'tanggal_mulai' => $date,
            'session' => 'pagi',
            'jumlah_penumpang' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'route_id' => $route->id,
            'vehicle_id' => $travelVehicle->id,
        ]);
    }

    public function test_rental_rejects_a_vehicle_reserved_for_travel_on_the_same_date(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $driver = User::factory()->create(['role' => 'driver']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'sopir_id' => $driver->id,
            'nama' => 'Mobil Travel Terpakai',
            'plat_nomor' => 'ABC 2277',
            'jenis' => 'MPV',
            'kapasitas_penumpang' => 4,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $date = today()->addDay()->toDateString();

        Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'service_type' => 'travel',
            'session' => 'pagi',
            'jumlah_penumpang' => 1,
            'tanggal_mulai' => $date,
            'tanggal_selesai' => $date,
            'status' => 'pending',
            'total_harga' => 150000,
        ]);

        $response = $this->actingAs($customer)->post(route('bookings.store'), [
            'service_type' => 'rental',
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => $date,
            'with_driver' => '1',
            'duration' => '1',
        ]);

        $response->assertSessionHasErrors(['tanggal_mulai']);
    }

    public function test_travel_rejects_cities_that_do_not_match_the_selected_route(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $route = Route::create([
            'origin' => 'Kota Rute Asal',
            'destination' => 'Kota Rute Tujuan',
            'service_type' => 'travel',
            'price' => 150000,
        ]);
        RouteAssignment::create([
            'route_id' => $route->id,
            'mitra_id' => $mitra->id,
            'session' => 'pagi',
            'priority' => 1,
        ]);

        $response = $this->actingAs($customer)->post(route('bookings.store'), [
            'service_type' => 'travel',
            'route_id' => $route->id,
            'origin' => 'Kota Lain',
            'destination' => 'Kota Rute Tujuan',
            'tanggal_mulai' => today()->addDay()->toDateString(),
            'session' => 'pagi',
            'jumlah_penumpang' => 1,
        ]);

        $response->assertSessionHasErrors(['route_id']);
        $this->assertDatabaseCount('bookings', 0);
    }

    public function test_cancelling_booking_restores_vehicle_status(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Mobil C',
            'plat_nomor' => 'ABC 3333',
            'jenis' => 'mobil',
            'status' => 'disewa',
            'harga_sewa_tanpa_sopir_per_hari' => 110000, 'harga_sewa_dengan_sopir_per_hari' => 110000,
            'is_approved' => true,
        ]);

        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2026-10-01',
            'tanggal_selesai' => '2026-10-03',
            'status' => 'pending',
            'total_harga' => 220000,
        ]);

        $response = $this->actingAs($customer)->post(route('bookings.cancel', $booking));

        $response->assertRedirect();
        $booking->refresh();
        $this->assertSame('cancelled', $booking->status);
        $vehicle->refresh();
        $this->assertSame('tersedia', $vehicle->status);
    }

    public function test_generating_ticket_marks_ticket_created_without_completing_booking(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'nama' => 'Mobil Tiket',
            'plat_nomor' => 'ABC 4444',
            'jenis' => 'mobil',
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $booking = Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $vehicle->id,
            'tanggal_mulai' => '2026-11-01',
            'tanggal_selesai' => '2026-11-01',
            'status' => 'pending',
            'payment_status' => Booking::PAYMENT_PAID,
            'ticket_status' => Booking::TICKET_NOT_CREATED,
            'total_harga' => 100000,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.bookings.generate-ticket', $booking));

        $response->assertRedirect();
        $booking->refresh();
        $this->assertSame(Booking::TICKET_CREATED, $booking->ticket_status);
        $this->assertSame('sopir_assigned', $booking->status);
        $this->assertNotNull($booking->ticket_number);
    }

    public function test_travel_rejects_a_departure_session_that_has_passed_today(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 4, 13, 0));
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->post(route('bookings.store'), [
            'service_type' => 'travel',
            'tanggal_mulai' => '2026-08-04',
            'session' => 'pagi',
        ]);

        $response->assertSessionHasErrors(['session']);
        $this->assertDatabaseCount('bookings', 0);
        Carbon::setTestNow();
    }

    public function test_travel_rejects_a_departure_date_that_has_passed(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 8, 4, 7, 0));
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer)->post(route('bookings.store'), [
            'service_type' => 'travel',
            'tanggal_mulai' => '2026-08-03',
            'session' => 'siang',
        ]);

        $response->assertSessionHasErrors(['tanggal_mulai']);
        $this->assertDatabaseCount('bookings', 0);
        Carbon::setTestNow();
    }

    public function test_travel_booking_moves_to_the_next_vehicle_when_the_first_is_full(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $driverOne = User::factory()->create(['role' => 'driver']);
        $driverTwo = User::factory()->create(['role' => 'driver']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $firstVehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'sopir_id' => $driverOne->id,
            'nama' => 'Mobil Prioritas',
            'plat_nomor' => 'ABC 5555',
            'jenis' => 'MPV',
            'kapasitas_penumpang' => 2,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $secondVehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'sopir_id' => $driverTwo->id,
            'nama' => 'Mobil Berikutnya',
            'plat_nomor' => 'ABC 6666',
            'jenis' => 'MPV',
            'kapasitas_penumpang' => 2,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $route = Route::create([
            'origin' => 'Kota A',
            'destination' => 'Kota B',
            'service_type' => 'travel',
            'price' => 150000,
        ]);
        RouteAssignment::create(['route_id' => $route->id, 'mitra_id' => $mitra->id, 'session' => 'pagi', 'priority' => 1]);
        $date = today()->addDay()->toDateString();

        Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $firstVehicle->id,
            'route_id' => $route->id,
            'service_type' => 'travel',
            'session' => 'pagi',
            'jumlah_penumpang' => 2,
            'tanggal_mulai' => $date,
            'tanggal_selesai' => $date,
            'status' => 'pending',
            'total_harga' => 150000,
        ]);

        $response = $this->actingAs($customer)->post(route('bookings.store'), [
            'service_type' => 'travel',
            'route_id' => $route->id,
            'tanggal_mulai' => $date,
            'session' => 'pagi',
            'jumlah_penumpang' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'route_id' => $route->id,
            'vehicle_id' => $secondVehicle->id,
            'jumlah_penumpang' => 1,
        ]);

        $fullResponse = $this->actingAs($customer)->post(route('bookings.store'), [
            'service_type' => 'travel',
            'route_id' => $route->id,
            'tanggal_mulai' => $date,
            'session' => 'pagi',
            'jumlah_penumpang' => 2,
        ]);

        $fullResponse->assertSessionHasErrors(['session']);
    }

    public function test_new_travel_booking_moves_after_the_driver_marks_first_vehicle_departed(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(7, 0));
        $customer = User::factory()->create(['role' => 'customer']);
        $driverOne = User::factory()->create(['role' => 'driver']);
        $driverTwo = User::factory()->create(['role' => 'driver']);
        $mitra = User::factory()->create(['role' => 'mitra']);
        $firstVehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'sopir_id' => $driverOne->id,
            'nama' => 'Mobil Berangkat',
            'plat_nomor' => 'ABC 7777',
            'jenis' => 'MPV',
            'kapasitas_penumpang' => 4,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $secondVehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'sopir_id' => $driverTwo->id,
            'nama' => 'Mobil Cadangan',
            'plat_nomor' => 'ABC 8888',
            'jenis' => 'MPV',
            'kapasitas_penumpang' => 4,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $route = Route::create([
            'origin' => 'Kota C',
            'destination' => 'Kota D',
            'service_type' => 'travel',
            'price' => 175000,
        ]);
        $firstAssignment = RouteAssignment::create(['route_id' => $route->id, 'mitra_id' => $mitra->id, 'session' => 'pagi', 'priority' => 1]);

        Booking::create([
            'pelanggan_id' => $customer->id,
            'vehicle_id' => $firstVehicle->id,
            'sopir_id' => $driverOne->id,
            'route_id' => $route->id,
            'service_type' => 'travel',
            'session' => 'pagi',
            'jumlah_penumpang' => 2,
            'tanggal_mulai' => today()->toDateString(),
            'tanggal_selesai' => today()->toDateString(),
            'status' => 'pending',
            'total_harga' => 175000,
        ]);

        $driverResponse = $this->actingAs($driverOne)->get(route('driver.dashboard'));
        $driverResponse->assertOk();
        $driverResponse->assertSee('2 / 4');

        $departureResponse = $this->actingAs($driverOne)->post(route('driver.route-assignments.depart', [$firstAssignment, $firstVehicle]));
        $departureResponse->assertRedirect();

        $date = today()->toDateString();
        $response = $this->actingAs($customer)->post(route('bookings.store'), [
            'service_type' => 'travel',
            'route_id' => $route->id,
            'tanggal_mulai' => $date,
            'session' => 'pagi',
            'jumlah_penumpang' => 1,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'route_id' => $route->id,
            'vehicle_id' => $secondVehicle->id,
        ]);
        Carbon::setTestNow();
    }

    public function test_customer_travel_form_uses_the_mitra_assigned_to_the_session(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $mitra = User::factory()->create(['role' => 'mitra', 'name' => 'Mitra Ende']);
        $driver = User::factory()->create(['role' => 'driver']);
        $vehicle = Vehicle::create([
            'mitra_id' => $mitra->id,
            'sopir_id' => $driver->id,
            'nama' => 'Mobil Ende',
            'plat_nomor' => 'B 9999 EN',
            'jenis' => 'MPV',
            'kapasitas_penumpang' => 7,
            'status' => 'tersedia',
            'harga_sewa_tanpa_sopir_per_hari' => 100000, 'harga_sewa_dengan_sopir_per_hari' => 100000,
            'is_approved' => true,
        ]);
        $route = Route::create([
            'origin' => 'Ende',
            'destination' => 'Mbay',
            'service_type' => 'travel',
            'price' => 150000,
        ]);
        RouteAssignment::create([
            'route_id' => $route->id,
            'mitra_id' => $mitra->id,
            'session' => 'pagi',
            'priority' => 1,
        ]);

        $response = $this->actingAs($customer)->get(route('bookings.create'));

        $response->assertOk();
        $response->assertSee('Ende');
        $response->assertSee('Mitra Ende');
        $response->assertSee($vehicle->nama);
    }
}
