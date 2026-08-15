<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Route;
use App\Models\RouteAssignment;
use App\Models\TravelDeparture;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly RevenueService $revenueService,
    ) {}

    public function calculateTotalPrice(Vehicle $vehicle, string $tanggalMulai, string $tanggalSelesai, string $serviceType = 'rental', ?int $routeId = null, float $durationDays = 1): int
    {
        if ($serviceType === 'travel' && $routeId) {
            $route = Route::where('service_type', 'travel')->find($routeId);

            return $route?->price ?? 0;
        }

        $days = (int) ceil($durationDays);

        $total = $vehicle->harga_sewa_per_hari * $days;

        if ($durationDays === 0.5) {
            $total = (int) ceil($vehicle->harga_sewa_per_hari / 2);
        }

        return $total;
    }

    public function hasVehicleConflict(int $vehicleId, string $tanggalMulai, string $tanggalSelesai, ?int $excludeBookingId = null): bool
    {
        $hasBookingConflict = Booking::where('vehicle_id', $vehicleId)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->where(function ($query) use ($tanggalMulai, $tanggalSelesai) {
                $query->whereDate('tanggal_mulai', '>=', $tanggalMulai)
                    ->whereDate('tanggal_mulai', '<=', $tanggalSelesai)
                    ->orWhere(function ($sub) use ($tanggalMulai, $tanggalSelesai) {
                        $sub->whereDate('tanggal_selesai', '>=', $tanggalMulai)
                            ->whereDate('tanggal_selesai', '<=', $tanggalSelesai);
                    })
                    ->orWhere(function ($sub) use ($tanggalMulai, $tanggalSelesai) {
                        $sub->whereDate('tanggal_mulai', '<=', $tanggalMulai)
                            ->whereDate('tanggal_selesai', '>=', $tanggalSelesai);
                    });
            })
            ->exists();

        if ($hasBookingConflict) {
            return true;
        }

        return TravelDeparture::where('vehicle_id', $vehicleId)
            ->whereDate('departure_date', '>=', $tanggalMulai)
            ->whereDate('departure_date', '<=', $tanggalSelesai)
            ->exists();
    }

    public function hasDriverConflict(int $driverId, string $tanggalMulai, string $tanggalSelesai, ?int $excludeBookingId = null): bool
    {
        return Booking::where('sopir_id', $driverId)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->where(function ($query) use ($tanggalMulai, $tanggalSelesai) {
                $query->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                    ->orWhereBetween('tanggal_selesai', [$tanggalMulai, $tanggalSelesai])
                    ->orWhere(function ($sub) use ($tanggalMulai, $tanggalSelesai) {
                        $sub->where('tanggal_mulai', '<=', $tanggalMulai)
                            ->where('tanggal_selesai', '>=', $tanggalSelesai);
                    });
            })
            ->exists();
    }

    public function createBooking(
        int $customerId,
        ?int $vehicleId,
        string $tanggalMulai,
        string $tanggalSelesai,
        string $serviceType = 'rental',
        ?string $origin = null,
        ?string $destination = null,
        ?string $flightNumber = null,
        ?string $notes = null,
        ?int $routeId = null,
        ?string $session = null,
        bool $withDriver = true,
        float $durationDays = 1,
        int $passengerCount = 1,
    ): Booking {
        return DB::transaction(function () use (
            $customerId,
            $vehicleId,
            $tanggalMulai,
            $tanggalSelesai,
            $serviceType,
            $origin,
            $destination,
            $flightNumber,
            $notes,
            $routeId,
            $session,
            $withDriver,
            $durationDays,
            $passengerCount,
        ): Booking {
            if ($serviceType === 'rental' && !$vehicleId) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'Kendaraan rental wajib dipilih.',
                ]);
            }

            $vehicle = $vehicleId
                ? Vehicle::where('is_approved', true)
                    ->where('status', 'tersedia')
                    ->lockForUpdate()
                    ->findOrFail($vehicleId)
                : new Vehicle(['harga_sewa_per_hari' => 0]);

            if ($serviceType === 'rental') {
                if ($this->hasVehicleConflict($vehicle->id, $tanggalMulai, $tanggalSelesai)) {
                    throw ValidationException::withMessages([
                        'tanggal_mulai' => 'Unit ini sudah memiliki booking pada rentang tanggal yang dipilih.',
                    ]);
                }

                if ($withDriver && $vehicle->sopir_id && $this->hasDriverConflict($vehicle->sopir_id, $tanggalMulai, $tanggalSelesai)) {
                    throw ValidationException::withMessages([
                        'tanggal_mulai' => 'Sopir kendaraan sedang bertugas pada rentang tanggal yang dipilih.',
                    ]);
                }
            }

            $total = $this->calculateTotalPrice($vehicle, $tanggalMulai, $tanggalSelesai, $serviceType, $routeId, $durationDays);

            // Auto-assign vehicle and driver from route for travel service.
            $finalVehicleId = $vehicleId;
            $finalSopirId = $withDriver ? $vehicle->sopir_id : null;

            if ($serviceType === 'travel' && $routeId && $session) {
                [, $assignedVehicle] = $this->findTravelVehicle($routeId, $session, $tanggalMulai, $passengerCount);
                $finalVehicleId = $assignedVehicle->id;
                $finalSopirId = $assignedVehicle->sopir_id;
            }

            return Booking::create([
                'pelanggan_id'    => $customerId,
                'vehicle_id'      => $finalVehicleId,
                'sopir_id'        => $finalSopirId,
                'route_id'        => $routeId,
                'service_type'    => $serviceType,
                'session'         => $session,
                'jumlah_penumpang' => $passengerCount,
                'tanggal_mulai'   => $tanggalMulai,
                'tanggal_selesai' => $tanggalSelesai,
                'origin'          => $origin,
                'destination'     => $destination,
                'flight_number'   => $flightNumber,
                'notes'           => $notes,
                'status'          => 'pending',
                'payment_status'  => Booking::PAYMENT_UNPAID,
                'ticket_status'   => Booking::TICKET_NOT_CREATED,
                'total_harga'     => $total,
                'with_driver'     => $withDriver,
            ]);
        });
    }

    private function findTravelVehicle(int $routeId, string $session, string $departureDate, int $passengerCount): array
    {
        $assignment = RouteAssignment::where('route_id', $routeId)
            ->where('session', $session)
            ->with(['mitra', 'vehicle'])
            ->lockForUpdate()
            ->first();

        $mitraId = $assignment?->mitra_id ?: $assignment?->vehicle?->mitra_id;
        if (!$assignment || !$mitraId) {
            throw ValidationException::withMessages([
                'session' => 'Mitra belum ditentukan untuk sesi travel ini.',
            ]);
        }

        $vehicles = Vehicle::where('mitra_id', $mitraId)
            ->where('is_approved', true)
            ->where('status', 'tersedia')
            ->whereNotNull('sopir_id')
            ->with('sopir')
            ->orderByRaw('CASE WHEN prioritas_travel > 0 THEN 0 ELSE 1 END')
            ->orderBy('prioritas_travel')
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        foreach ($vehicles as $vehicle) {
            if (TravelDeparture::where('route_assignment_id', $assignment->id)
                ->where('vehicle_id', $vehicle->id)
                ->whereDate('departure_date', $departureDate)
                ->exists()) {
                continue;
            }

            $hasRentalConflict = Booking::where('vehicle_id', $vehicle->id)
                ->where('service_type', '!=', 'travel')
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->whereDate('tanggal_mulai', '<=', $departureDate)
                ->whereDate('tanggal_selesai', '>=', $departureDate)
                ->exists();

            if ($hasRentalConflict) {
                continue;
            }

            $passengers = Booking::where('route_id', $routeId)
                ->where('session', $session)
                ->where('service_type', 'travel')
                ->where('vehicle_id', $vehicle->id)
                ->whereDate('tanggal_mulai', $departureDate)
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->sum('jumlah_penumpang');

            if ($passengers + $passengerCount <= $vehicle->kapasitas_penumpang) {
                return [$assignment, $vehicle];
            }
        }

        throw ValidationException::withMessages([
            'session' => 'Semua kendaraan untuk sesi ini sudah penuh, sudah berangkat, atau belum memiliki sopir.',
        ]);
    }

    public function assignDriver(Booking $booking, int $driverId): void
    {
        $booking->update([
            'sopir_id' => $driverId,
        ]);

        $this->notificationService->log(
            $booking->pelanggan_id,
            'booking_assigned',
            'Sopir telah ditugaskan untuk booking Anda.',
            Booking::class,
            $booking->id
        );

        $this->notificationService->log(
            $driverId,
            'booking_assigned_driver',
            'Anda memiliki booking baru yang ditugaskan.',
            Booking::class,
            $booking->id
        );
    }

    public function markCompleted(Booking $booking): void
    {
        if ($booking->payment_status !== Booking::PAYMENT_PAID || $booking->ticket_status !== Booking::TICKET_CREATED) {
            throw ValidationException::withMessages([
                'booking' => 'Booking belum lunas atau tiket belum dibuat.',
            ]);
        }

        $booking->transitionTo(Booking::STATUS_COMPLETED);

        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'tersedia']);
        }

        $this->notificationService->log(
            $booking->pelanggan_id,
            'booking_completed',
            'Booking Anda telah selesai.',
            Booking::class,
            $booking->id
        );

        $mitraId = $booking->vehicle?->mitra_id;

        if ($mitraId) {
            $this->revenueService->createPayout($booking, $mitraId);
        }
    }

    public function cancel(Booking $booking): void
    {
        if (in_array($booking->payment_status, [Booking::PAYMENT_PENDING, Booking::PAYMENT_PAID], true)) {
            throw ValidationException::withMessages([
                'booking' => 'Booking dengan pembayaran yang sedang diproses atau sudah lunas memerlukan penanganan admin.',
            ]);
        }

        $booking->transitionTo(Booking::STATUS_CANCELLED);

        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'tersedia']);
        }

        $this->notificationService->log(
            $booking->pelanggan_id,
            'booking_cancelled',
            'Booking Anda telah dibatalkan.',
            Booking::class,
            $booking->id
        );
    }

    public function cancelByAdmin(Booking $booking): void
    {
        if (in_array($booking->status, [Booking::STATUS_CANCELLED, Booking::STATUS_COMPLETED], true)) {
            return;
        }

        $booking->transitionTo(Booking::STATUS_CANCELLED);

        if ($booking->vehicle) {
            $booking->vehicle->update(['status' => 'tersedia']);
        }

        $this->notificationService->log(
            $booking->pelanggan_id,
            'booking_cancelled_admin',
            'Booking Anda dibatalkan oleh admin setelah proses refund.',
            Booking::class,
            $booking->id
        );
    }
}
