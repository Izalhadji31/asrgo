<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignDriverRequest;
use App\Http\Requests\StoreBookingRequest;
use App\Models\AuditLog;
use App\Models\Booking;
use App\Models\Route;
use App\Models\TravelDeparture;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\BookingService;
use App\Services\NotificationService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly PaymentService $paymentService,
        private readonly NotificationService $notificationService,
    ) {}

    public function index()
    {
        $bookings = Booking::with(['vehicle', 'pelanggan', 'sopir', 'review', 'passengers'])->latest();

        if (Auth::user()->role === 'customer') {
            $bookings = $bookings->where('pelanggan_id', Auth::id());

            return view('customer.bookings.index', [
                'bookings' => $bookings->paginate(15)->withQueryString(),
            ]);
        }

        $bookings = $bookings->where('sopir_id', Auth::id());

        return view('driver.bookings.index', [
            'bookings' => $bookings->paginate(15)->withQueryString(),
        ]);
    }

    public function adminBoard(Request $request)
    {
        $bookingsQuery = Booking::with(['vehicle', 'pelanggan', 'sopir', 'passengers'])->latest();
        $ticketStatus = $request->query('ticket_status');
        $ticketStatus = in_array($ticketStatus, [Booking::TICKET_NOT_CREATED, Booking::TICKET_CREATED], true)
            ? $ticketStatus
            : null;
        $paymentStatus = $request->query('payment_status');
        $paymentStatus = in_array($paymentStatus, [
            Booking::PAYMENT_UNPAID,
            Booking::PAYMENT_PENDING,
            Booking::PAYMENT_PAID,
            Booking::PAYMENT_FAILED,
            Booking::PAYMENT_EXPIRED,
        ], true) ? $paymentStatus : null;

        $search = trim((string) $request->query('q', ''));

        $ticketCounts = [
            'all' => (clone $bookingsQuery)->count(),
            Booking::TICKET_NOT_CREATED => (clone $bookingsQuery)->where('ticket_status', Booking::TICKET_NOT_CREATED)->count(),
            Booking::TICKET_CREATED => (clone $bookingsQuery)->where('ticket_status', Booking::TICKET_CREATED)->count(),
        ];

        $paymentCounts = [
            'all' => (clone $bookingsQuery)->count(),
            Booking::PAYMENT_UNPAID => (clone $bookingsQuery)->where('payment_status', Booking::PAYMENT_UNPAID)->count(),
            Booking::PAYMENT_PENDING => (clone $bookingsQuery)->where('payment_status', Booking::PAYMENT_PENDING)->count(),
            Booking::PAYMENT_PAID => (clone $bookingsQuery)->where('payment_status', Booking::PAYMENT_PAID)->count(),
            Booking::PAYMENT_FAILED => (clone $bookingsQuery)->where('payment_status', Booking::PAYMENT_FAILED)->count(),
            Booking::PAYMENT_EXPIRED => (clone $bookingsQuery)->where('payment_status', Booking::PAYMENT_EXPIRED)->count(),
        ];

        $bookings = (clone $bookingsQuery)
            ->when($ticketStatus, fn ($query) => $query->where('ticket_status', $ticketStatus))
            ->when($paymentStatus, fn ($query) => $query->where('payment_status', $paymentStatus))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('ticket_number', 'like', "%{$search}%")
                        ->orWhereHas('pelanggan', fn ($q2) => $q2->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('vehicle', fn ($q2) => $q2->where('nama', 'like', "%{$search}%")->orWhere('plat_nomor', 'like', "%{$search}%"));
                });
            })
            ->paginate(20)
            ->withQueryString();

        return view('bookings.admin-board', [
            'bookings' => $bookings,
            'ticketCounts' => $ticketCounts,
            'activeTicketStatus' => $ticketStatus,
            'activePaymentStatus' => $paymentStatus,
            'paymentCounts' => $paymentCounts,
            'search' => $search,
            'drivers' => User::where('role', 'driver')->get(),
            'vehicles' => Vehicle::with('sopir')->where('is_approved', true)->where('status', 'tersedia')->get(),
        ]);
    }

    public function create()
    {
        $vehicles = Vehicle::where('is_approved', true)
            ->where('status', 'tersedia')
            ->get();

        $vehiclesData = $vehicles->map(fn ($vehicle) => [
            'id' => $vehicle->id,
            'nama' => $vehicle->nama,
            'plat_nomor' => $vehicle->plat_nomor,
            'foto' => $vehicle->foto_url,
            'jenis' => $vehicle->jenis,
            'harga' => $vehicle->harga_sewa_dengan_sopir_per_hari,
            'harga_tanpa_sopir' => $vehicle->harga_sewa_tanpa_sopir_per_hari,
            'harga_dengan_sopir' => $vehicle->harga_sewa_dengan_sopir_per_hari,
            'has_sopir' => (bool) $vehicle->sopir_id,
        ])->values();

        $routes = Route::with(['assignments.mitra.vehicles.sopir'])
            ->where('service_type', 'travel')
            ->whereHas('assignments', fn ($query) => $query->whereNotNull('mitra_id'))
            ->latest()
            ->get();

        $routesData = $routes->map(fn ($r) => [
            'id' => $r->id,
            'origin' => $r->origin,
            'destination' => $r->destination,
            'price' => $r->price,
        ]);

        $activeBookings = Booking::whereNotNull('vehicle_id')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->get(['vehicle_id', 'tanggal_mulai', 'tanggal_selesai']);

        $vehicleReservations = $activeBookings
            ->groupBy('vehicle_id')
            ->map(fn ($bookings) => $bookings->map(fn ($booking) => [
                'start' => $booking->tanggal_mulai->toDateString(),
                'end' => $booking->tanggal_selesai->toDateString(),
            ])->values())
            ->toArray();

        TravelDeparture::whereNotNull('vehicle_id')
            ->get(['vehicle_id', 'departure_date'])
            ->each(function ($departure) use (&$vehicleReservations) {
                $vehicleReservations[$departure->vehicle_id][] = [
                    'start' => $departure->departure_date->toDateString(),
                    'end' => $departure->departure_date->toDateString(),
                ];
            });

        $routeIds = $routes->pluck('id');
        $travelBookingCounts = Booking::whereIn('route_id', $routeIds)
            ->where('service_type', 'travel')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->get(['route_id', 'session', 'vehicle_id', 'tanggal_mulai', 'jumlah_penumpang'])
            ->groupBy(fn ($booking) => implode('|', [
                $booking->route_id,
                $booking->session,
                $booking->vehicle_id,
                $booking->tanggal_mulai->toDateString(),
            ]))
            ->map(fn ($bookings) => $bookings->sum('jumlah_penumpang'));

        $travelDepartures = TravelDeparture::whereIn('route_id', $routeIds)
            ->get(['route_assignment_id', 'vehicle_id', 'departure_date'])
            ->map(fn ($departure) => implode('|', [
                $departure->route_assignment_id,
                $departure->vehicle_id,
                $departure->departure_date->toDateString(),
            ]))
            ->values();

        $routeAssignments = $routes->flatMap(function ($r) {
            return $r->assignments->flatMap(function ($a) use ($r) {
                $vehicles = $a->mitra?->vehicles
                    ->where('is_approved', true)
                    ->sortBy(fn ($vehicle) => [
                        $vehicle->prioritas_travel > 0 ? 0 : 1,
                        $vehicle->prioritas_travel ?: PHP_INT_MAX,
                        $vehicle->created_at?->timestamp ?: PHP_INT_MAX,
                        $vehicle->id,
                    ]) ?? collect();

                return $vehicles->map(function ($vehicle) use ($r, $a) {
                    return (object) [
                        'id' => $r->id,
                        'assignment_id' => $a->id,
                        'session' => $a->session,
                        'priority' => $a->priority,
                        'mitra' => $a->mitra?->name,
                        'vehicle' => (object) [
                            'id' => $vehicle->id,
                            'nama' => $vehicle->nama,
                            'plat_nomor' => $vehicle->plat_nomor,
                            'foto' => $vehicle->foto_url,
                            'jenis' => $vehicle->jenis,
                            'kapasitas_penumpang' => $vehicle->kapasitas_penumpang,
                        ],
                        'driver' => $vehicle->sopir ? (object) [
                            'id' => $vehicle->sopir->id,
                            'name' => $vehicle->sopir->name,
                        ] : null,
                    ];
                });
            });
        });

        return view('customer.bookings.create', compact(
            'vehicles',
            'vehiclesData',
            'routes',
            'routesData',
            'vehicleReservations',
            'routeAssignments',
            'travelBookingCounts',
            'travelDepartures'
        ));
    }

    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['vehicle', 'sopir', 'pelanggan', 'review', 'passengers']);

        return view('customer.bookings.show', compact('booking'));
    }

    public function store(StoreBookingRequest $request)
    {
        $vehicleId = $request->vehicle_id ?: null;
        $duration = $request->service_type === 'rental' ? (float) $request->duration : 1;

        if ($request->service_type === 'rental') {
            $days = (int) ceil($duration);
            $tanggalSelesai = Carbon::parse($request->tanggal_mulai)->addDays(max(0, $days - 1))->format('Y-m-d');
        } else {
            $tanggalSelesai = $request->tanggal_selesai ?? $request->tanggal_mulai;
        }

        // For travel service, check conflict only if vehicle is manually selected
        // Otherwise, route will auto-assign vehicle
        if ($request->service_type === 'rental' && $vehicleId) {
            if ($this->bookingService->hasVehicleConflict($vehicleId, $request->tanggal_mulai, $tanggalSelesai)) {
                return back()->withErrors(['tanggal_mulai' => 'Unit ini sudah memiliki booking pada rentang tanggal yang dipilih.'])->withInput();
            }
        }

        $booking = $this->bookingService->createBooking(
            customerId: Auth::id(),
            vehicleId: $vehicleId,
            tanggalMulai: $request->tanggal_mulai,
            tanggalSelesai: $tanggalSelesai,
            serviceType: $request->service_type,
            origin: $request->origin,
            destination: $request->destination,
            flightNumber: $request->flight_number,
            notes: $request->notes,
            routeId: $request->route_id,
            session: $request->session,
            withDriver: $request->boolean('with_driver', true),
            durationDays: $duration,
            passengerCount: (int) ($request->jumlah_penumpang ?: 1),
            passengers: $request->input('passengers', []),
            contactHp: $request->contact_hp,
        );

        return redirect()->route('payments.show', $booking)->with('success', 'Booking berhasil dibuat. Selesaikan pembayaran untuk melanjutkan.');
    }

    public function assignDriver(AssignDriverRequest $request, Booking $booking)
    {
        $this->authorize('assign', $booking);

        if ($this->bookingService->hasDriverConflict($request->sopir_id, $booking->tanggal_mulai, $booking->tanggal_selesai, $booking->id)) {
            return back()->withErrors(['sopir_id' => 'Sopir ini sedang bertugas pada rentang tanggal booking tersebut.'])->withInput();
        }

        $this->bookingService->assignDriver($booking, $request->sopir_id);

        AuditLog::record('assign_driver', 'Menugaskan sopir ke booking #'.$booking->id, Booking::class, $booking->id);

        $redirectRoute = Auth::user()->role === 'admin'
            ? route('admin.bookings.index')
            : route('bookings.index');

        return redirect($redirectRoute)->with('success', 'Sopir berhasil ditugaskan.');
    }

    public function assignVehicle(Request $request, Booking $booking)
    {
        $this->authorize('assign', $booking);

        $validated = $request->validate([
            'vehicle_id' => [
                'required',
                'integer',
                Rule::exists('vehicles', 'id')->where(fn ($query) => $query
                    ->where('is_approved', true)
                    ->where('status', 'tersedia')),
            ],
        ]);

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        if ($this->bookingService->hasVehicleConflict($vehicle->id, $booking->tanggal_mulai, $booking->tanggal_selesai, $booking->id)) {
            return back()->withErrors(['vehicle_id' => 'Unit ini sudah memiliki booking pada tanggal tersebut.'])->withInput();
        }

        $booking->update([
            'vehicle_id' => $vehicle->id,
            'sopir_id' => $vehicle->sopir_id,
        ]);

        AuditLog::record('assign_vehicle', 'Menugaskan kendaraan '.$vehicle->nama.' ke booking #'.$booking->id, Booking::class, $booking->id);

        return back()->with('success', 'Kendaraan berhasil ditugaskan ke booking.');
    }

    public function generateTicket(Booking $booking)
    {
        $this->authorize('assign', $booking);

        if (in_array($booking->status, ['cancelled', 'completed'], true)) {
            return back()->withErrors(['booking' => 'Booking yang sudah selesai atau dibatalkan tidak dapat dibuatkan tiket.']);
        }

        if ($booking->payment_status !== Booking::PAYMENT_PAID) {
            return back()->withErrors(['payment' => 'Tiket hanya dapat dibuat setelah pembayaran booking berhasil.']);
        }

        if (! $booking->vehicle_id) {
            return back()->withErrors(['vehicle_id' => 'Silakan assign kendaraan terlebih dahulu.']);
        }

        if ($booking->ticket_number) {
            return back()->with('info', 'Tiket sudah dibuat sebelumnya.');
        }

        $booking->transitionTo(Booking::STATUS_ASSIGNED);
        $booking->update([
            'ticket_number' => 'TKT-'.strtoupper(uniqid()),
            'ticket_status' => Booking::TICKET_CREATED,
        ]);

        AuditLog::record('generate_ticket', 'Membuat tiket '.$booking->ticket_number.' untuk booking #'.$booking->id, Booking::class, $booking->id);

        return back()->with('success', 'Tiket berhasil dibuat.');
    }

    public function refund(Booking $booking)
    {
        $this->authorize('refund', $booking);

        try {
            $this->processApprovedRefund($booking);
        } catch (Throwable $exception) {
            Log::error('Booking refund failed.', [
                'booking_id' => $booking->id,
                'exception' => $exception,
            ]);

            return back()->withErrors(['refund' => $exception->getMessage()]);
        }

        return back()->with('success', 'Refund booking berhasil diajukan dan booking dibatalkan.');
    }

    public function requestRefund(Request $request, Booking $booking)
    {
        $this->authorize('requestRefund', $booking);

        $validated = $request->validate([
            'refund_reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $booking->forceFill([
            'refund_status' => Booking::REFUND_REQUESTED,
            'refund_reason' => $validated['refund_reason'],
            'refund_rejection_reason' => null,
            'refund_requested_at' => now(),
            'refund_reviewed_at' => null,
        ])->save();

        $this->notificationService->log(
            $booking->pelanggan_id,
            'refund_requested',
            'Pengajuan refund Anda sudah diterima dan sedang menunggu pemeriksaan admin.',
            Booking::class,
            $booking->id
        );

        return back()->with('success', 'Pengajuan refund berhasil dikirim dan sedang ditinjau admin.');
    }

    public function approveRefund(Booking $booking)
    {
        $this->authorize('approveRefund', $booking);

        try {
            $this->processApprovedRefund($booking);
        } catch (Throwable $exception) {
            Log::error('Booking refund approval failed.', [
                'booking_id' => $booking->id,
                'exception' => $exception,
            ]);

            return back()->withErrors(['refund' => $exception->getMessage()]);
        }

        AuditLog::record('approve_refund', 'Menyetujui refund booking #'.$booking->id, Booking::class, $booking->id);

        return back()->with('success', 'Refund disetujui dan sudah diajukan ke Midtrans.');
    }

    public function rejectRefund(Request $request, Booking $booking)
    {
        $this->authorize('rejectRefund', $booking);

        $validated = $request->validate([
            'refund_rejection_reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $booking->forceFill([
            'refund_status' => Booking::REFUND_REJECTED,
            'refund_rejection_reason' => $validated['refund_rejection_reason'],
            'refund_reviewed_at' => now(),
        ])->save();

        $this->notificationService->log(
            $booking->pelanggan_id,
            'refund_rejected',
            'Pengajuan refund Anda ditolak admin: '.$validated['refund_rejection_reason'],
            Booking::class,
            $booking->id
        );

        AuditLog::record('reject_refund', 'Menolak refund booking #'.$booking->id, Booking::class, $booking->id);

        return back()->with('success', 'Pengajuan refund ditolak.');
    }

    public function showTicket(Booking $booking)
    {
        if (! $booking->ticket_number) {
            abort(404);
        }

        if (Auth::id() !== $booking->pelanggan_id && Auth::user()->role !== 'admin') {
            abort(403);
        }

        return view('bookings.ticket', [
            'booking' => $booking->load(['vehicle', 'pelanggan', 'sopir']),
        ]);
    }

    public function markCompleted(Booking $booking)
    {
        $this->authorize('complete', $booking);

        if ($booking->status === 'completed') {
            return redirect()->back()->with('info', 'Booking sudah selesai.');
        }

        if ($booking->status === 'cancelled') {
            return redirect()->back()->with('info', 'Booking yang dibatalkan tidak dapat diselesaikan.');
        }

        if ($booking->payment_status !== Booking::PAYMENT_PAID || $booking->ticket_status !== Booking::TICKET_CREATED) {
            return redirect()->back()->withErrors(['booking' => 'Booking belum lunas atau tiket belum dibuat.']);
        }

        if ($booking->tanggal_mulai->isFuture()) {
            return redirect()->back()->withErrors(['booking' => 'Booking belum memasuki tanggal pelaksanaan.']);
        }

        $this->bookingService->markCompleted($booking);

        return redirect()->back()->with('success', 'Booking selesai dan payout dibuat.');
    }

    public function adminCancel(Booking $booking)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        if (in_array($booking->status, [Booking::STATUS_COMPLETED, Booking::STATUS_CANCELLED], true)) {
            return back()->withErrors(['booking' => 'Booking yang sudah selesai atau dibatalkan tidak dapat dibatalkan lagi.']);
        }

        try {
            $this->bookingService->cancel($booking);
        } catch (ValidationException $exception) {
            return back()->withErrors($exception->errors());
        }

        AuditLog::record('cancel_booking', 'Membatalkan booking #'.$booking->id, Booking::class, $booking->id);

        return back()->with('success', 'Booking #'.$booking->id.' berhasil dibatalkan. Customer sudah dinotifikasi.');
    }

    public function markAsFullyPaid(Booking $booking)
    {
        if ($booking->payment_status !== Booking::PAYMENT_PAID) {
            return redirect()->back()->withErrors(['booking' => 'Booking belum memiliki pembayaran yang diterima.']);
        }

        if ($booking->payment_scheme !== Booking::PAYMENT_SCHEME_DP) {
            return redirect()->back()->with('info', 'Booking ini sudah berstatus lunas.');
        }

        $booking->forceFill([
            'payment_scheme' => Booking::PAYMENT_SCHEME_FULL,
            'payment_paid_at' => now(),
        ])->save();

        AuditLog::record('mark_paid', 'Menandai lunas booking #'.$booking->id, Booking::class, $booking->id);

        $this->notificationService->log(
            $booking->pelanggan_id,
            'payment_settled',
            'Pelunasan booking Anda telah dikonfirmasi admin. Booking kini berstatus lunas.',
            Booking::class,
            $booking->id
        );

        return redirect()->back()->with('success', 'Booking ditandai lunas. Sisa pembayaran telah dikonfirmasi manual.');
    }

    public function cancel(Booking $booking)
    {
        $this->authorize('cancel', $booking);

        if (in_array($booking->status, ['cancelled', 'completed'], true)) {
            return redirect()->back()->with('info', 'Booking tidak bisa dibatalkan lagi.');
        }

        if ($booking->ticket_number) {
            return redirect()->back()->with('info', 'Booking yang sudah memiliki tiket tidak dapat dibatalkan dari halaman ini.');
        }

        if (in_array($booking->payment_status, [Booking::PAYMENT_PENDING, Booking::PAYMENT_PAID], true)) {
            return redirect()->back()->with('info', 'Booking dengan pembayaran yang sedang diproses atau sudah lunas memerlukan penanganan admin.');
        }

        $this->bookingService->cancel($booking);

        return redirect()->back()->with('success', 'Booking berhasil dibatalkan.');
    }

    private function processApprovedRefund(Booking $booking): void
    {
        $this->paymentService->refund($booking);
        $booking->forceFill(['refund_reviewed_at' => now()])->save();
        $this->bookingService->cancelByAdmin($booking);
    }
}
