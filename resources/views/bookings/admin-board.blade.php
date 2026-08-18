@extends('layouts.admin')

@section('content')
    <div class="space-y-6" x-data="paymentStatusWatcher(@js(route('payments.statuses')))">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Papan Booking</h1>
                <p class="text-sm text-slate-500">Daftar seluruh booking yang saat ini terdaftar di sistem.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">Kembali ke Ringkasan</a>
        </div>

        @if (session('success'))
            <div class="rounded-lg bg-green-100 p-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-red-100 p-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @php
            $stLabels = ['rental' => 'Rental', 'travel' => 'Travel'];
            $stColor = ['rental' => 'bg-blue-100 text-blue-700', 'travel' => 'bg-purple-100 text-purple-700'];
            $snLabels = ['pagi' => 'Pagi', 'siang' => 'Siang'];
            $snColor = ['pagi' => 'bg-sky-100 text-sky-700', 'siang' => 'bg-orange-100 text-orange-700'];
            $statusLabels = ['pending' => 'Aktif', 'sopir_assigned' => 'Aktif', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
            $ticketStatusStyles = [
                'not_created' => ['label' => 'Belum Dapat Tiket', 'class' => 'bg-amber-100 text-amber-700', 'dot' => 'bg-[#E8A33D]'],
                'created' => ['label' => 'Tiket Dibuat', 'class' => 'bg-emerald-100 text-emerald-700', 'dot' => 'bg-[#3F7D6C]'],
            ];
            $refundStatusLabels = [
                'requested' => ['label' => 'Refund Menunggu Review', 'class' => 'bg-orange-100 text-orange-700'],
                'rejected' => ['label' => 'Refund Ditolak', 'class' => 'bg-red-100 text-red-700'],
                'pending' => ['label' => 'Refund Diproses Midtrans', 'class' => 'bg-blue-100 text-blue-700'],
                'completed' => ['label' => 'Refund Selesai', 'class' => 'bg-emerald-100 text-emerald-700'],
                'failed' => ['label' => 'Refund Gagal', 'class' => 'bg-red-100 text-red-700'],
            ];
        @endphp

        <div class="grid gap-4 sm:grid-cols-3">
            <a href="{{ route('admin.bookings.index') }}" class="rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ !$activeTicketStatus ? 'border-blue-900 bg-blue-900 text-white' : 'border-slate-200 bg-white' }}">
                <p class="text-sm font-medium {{ !$activeTicketStatus ? 'text-blue-100' : 'text-slate-500' }}">Semua Booking</p>
                <p class="mt-2 font-[IBM_Plex_Mono] text-2xl font-semibold">{{ $ticketCounts['all'] }}</p>
            </a>
            <a href="{{ route('admin.bookings.index', ['ticket_status' => 'not_created']) }}" class="rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $activeTicketStatus === 'not_created' ? 'border-[#E8A33D] bg-amber-50' : 'border-slate-200 bg-white' }}">
                <p class="text-sm font-medium text-amber-700">Belum Dapat Tiket</p>
                <p class="mt-2 font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $ticketCounts['not_created'] }}</p>
                <p class="mt-1 text-xs text-slate-500">Perlu ditindaklanjuti admin</p>
            </a>
            <a href="{{ route('admin.bookings.index', ['ticket_status' => 'created']) }}" class="rounded-2xl border p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md {{ $activeTicketStatus === 'created' ? 'border-[#3F7D6C] bg-emerald-50' : 'border-slate-200 bg-white' }}">
                <p class="text-sm font-medium text-emerald-700">Tiket Dibuat</p>
                <p class="mt-2 font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $ticketCounts['created'] }}</p>
                <p class="mt-1 text-xs text-slate-500">Siap dilihat pelanggan</p>
            </a>
        </div>

        <form method="GET" action="{{ route('admin.bookings.index') }}" class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
            <div class="flex-[2]">
                <label for="q" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Cari Booking</label>
                <input id="q" name="q" type="text" value="{{ $search }}" placeholder="No. booking, nama/email customer, plat/nama kendaraan, no. tiket" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700">
            </div>
            <div class="flex-1">
                <label for="payment_status" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Filter Pembayaran</label>
                <select id="payment_status" name="payment_status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700">
                    <option value="">Semua Pembayaran ({{ $paymentCounts['all'] }})</option>
                    <option value="unpaid" @selected($activePaymentStatus === 'unpaid')>Belum Dibayar ({{ $paymentCounts['unpaid'] }})</option>
                    <option value="pending" @selected($activePaymentStatus === 'pending')>Menunggu ({{ $paymentCounts['pending'] }})</option>
                    <option value="paid" @selected($activePaymentStatus === 'paid')>Lunas ({{ $paymentCounts['paid'] }})</option>
                    <option value="failed" @selected($activePaymentStatus === 'failed')>Gagal ({{ $paymentCounts['failed'] }})</option>
                    <option value="expired" @selected($activePaymentStatus === 'expired')>Kedaluwarsa ({{ $paymentCounts['expired'] }})</option>
                </select>
            </div>
            <button class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Terapkan Filter</button>
            <a href="{{ route('admin.bookings.export', request()->query()) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100">Export CSV</a>
            @if ($activePaymentStatus)
                <a href="{{ route('admin.bookings.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600">Reset</a>
            @endif
        </form>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Status Booking</th>
                            <th class="px-5 py-3 font-medium">Status Tiket</th>
                            <th class="px-5 py-3 font-medium">Pembayaran</th>
                            <th class="px-5 py-3 font-medium">Layanan</th>
                            <th class="px-5 py-3 font-medium">Nomor Plat</th>
                            <th class="px-5 py-3 font-medium">Pelanggan</th>
                            <th class="px-5 py-3 font-medium">Unit</th>
                            <th class="px-5 py-3 font-medium">Sopir</th>
                            <th class="px-5 py-3 font-medium">Tanggal</th>
                            <th class="px-5 py-3 font-medium">Total</th>
                            <th class="px-5 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($bookings as $booking)
                            <tr class="odd:bg-white even:bg-slate-50/50 hover:bg-slate-100/70 transition">
                                 <td class="px-5 py-4">
                                     <div class="flex items-center gap-2">
                                         <span class="h-3 w-3 rounded-full @switch($booking->status)
                                            @case('pending') bg-blue-900 @break
                                            @case('sopir_assigned') bg-blue-900 @break
                                            @case('completed') bg-[#3F7D6C] @break
                                            @default bg-[#C1443C] @endswitch"></span>
                                         <span class="text-slate-600">{{ $statusLabels[$booking->status] ?? $booking->status }}</span>
                                         @if ($booking->service_type === 'travel' && $booking->departed())
                                             <span class="rounded-full bg-teal-100 px-2 py-0.5 text-xs font-semibold text-teal-700">Sudah Berangkat</span>
                                         @endif
                                     </div>
                                 </td>
                                 <td class="px-5 py-4">
                                     @php $ticketStyle = $ticketStatusStyles[$booking->ticket_status] ?? $ticketStatusStyles['not_created']; @endphp
                                     <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $ticketStyle['class'] }}">
                                         <span class="h-1.5 w-1.5 rounded-full {{ $ticketStyle['dot'] }}"></span>
                                         {{ $ticketStyle['label'] }}
                                     </span>
                                 </td>
                                 <td class="px-5 py-4">
                                      <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $booking->payment_status === 'paid' ? ($booking->payment_scheme === 'dp' ? 'bg-sky-100 text-sky-700' : 'bg-emerald-100 text-emerald-700') : 'bg-amber-100 text-amber-700' }}">
                                         @if ($booking->payment_status === 'paid' && $booking->payment_scheme === 'dp')
                                             DP 30% Dibayar
                                         @else
                                             {{ match ($booking->payment_status) {
                                                 'paid' => 'Lunas',
                                                 'pending' => 'Menunggu',
                                                 'failed' => 'Gagal',
                                                 'expired' => 'Kedaluwarsa',
                                                 default => 'Belum Dibayar',
                                              } }}
                                         @endif
                                      </span>
                                      @if ($booking->payment_status === 'paid' && $booking->payment_scheme === 'dp')
                                          <span class="mt-2 block rounded-full px-3 py-1 text-xs font-semibold bg-amber-100 text-amber-700">Sisa 70% belum lunas</span>
                                      @endif
                                      @if ($booking->refund_status !== 'none' && isset($refundStatusLabels[$booking->refund_status]))
                                          <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $refundStatusLabels[$booking->refund_status]['class'] }}">
                                              {{ $refundStatusLabels[$booking->refund_status]['label'] }}
                                          </span>
                                      @endif
                                  </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $stColor[$booking->service_type] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ $stLabels[$booking->service_type] ?? $booking->service_type }}
                                        </span>
                                        @if ($booking->session)
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $snColor[$booking->session] ?? 'bg-slate-100 text-slate-700' }}">
                                            {{ $snLabels[$booking->session] ?? $booking->session }}
                                        </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">{{ $booking->vehicle?->plat_nomor ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $booking->pelanggan?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $booking->vehicle?->nama ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $booking->sopir?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $booking->tanggal_mulai }} s/d {{ $booking->tanggal_selesai }}</td>
                                <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <button
                                        x-data
                                        x-on:click="$dispatch('open-modal', 'booking-detail-{{ $booking->id }}')"
                                        class="rounded-lg border border-[#E8A33D] px-3 py-1.5 text-sm font-medium text-[#E8A33D] transition hover:bg-[#E8A33D] hover:text-white focus:outline-none focus:ring-2 focus:ring-[#E8A33D]"
                                    >
                                        Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-5 py-6 text-center text-sm text-slate-500">Belum ada booking pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $bookings->links() }}
            </div>
        </div>
    </div>

    @foreach ($bookings as $booking)
        <x-modal name="booking-detail-{{ $booking->id }}" maxWidth="xl">
            <div class="p-6">
                @php $ticketStyle = $ticketStatusStyles[$booking->ticket_status] ?? $ticketStatusStyles['not_created']; @endphp
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Detail Booking #{{ $booking->id }}</h2>
                    <span class="h-3 w-3 rounded-full @switch($booking->status)
                        @case('pending') bg-blue-900 @break
                        @case('sopir_assigned') bg-blue-900 @break
                        @case('completed') bg-[#3F7D6C] @break
                        @default bg-[#C1443C] @endswitch"></span>
                </div>

                 <div class="mb-4 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-semibold
                        @switch($booking->status)
                            @case('pending') bg-blue-50 text-blue-900 @break
                            @case('sopir_assigned') bg-blue-50 text-blue-900 @break
                            @case('completed') bg-emerald-50 text-[#3F7D6C] @break
                            @default bg-red-50 text-[#C1443C] @endswitch">
                        {{ $statusLabels[$booking->status] ?? $booking->status }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-semibold {{ $ticketStyle['class'] }}">
                        <span class="h-2 w-2 rounded-full {{ $ticketStyle['dot'] }}"></span>
                        {{ $ticketStyle['label'] }}
                     </span>
                     <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-4 py-1.5 text-sm font-semibold text-amber-700">
                         Pembayaran: {{ $booking->payment_status === 'paid' ? 'Lunas' : ucfirst($booking->payment_status ?? 'unpaid') }}
                     </span>
                     @if ($booking->refund_status !== 'none' && isset($refundStatusLabels[$booking->refund_status]))
                         <span class="inline-flex items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-semibold {{ $refundStatusLabels[$booking->refund_status]['class'] }}">
                             {{ $refundStatusLabels[$booking->refund_status]['label'] }}
                         </span>
                     @endif
                  </div>

                 @if ($booking->refund_reason)
                     <div class="mb-4 rounded-lg border border-orange-200 bg-orange-50 p-4 text-sm text-orange-900">
                         <p class="text-xs font-semibold uppercase tracking-wide text-orange-700">Alasan refund pelanggan</p>
                         <p class="mt-1">{{ $booking->refund_reason }}</p>
                         @if ($booking->refund_rejection_reason)
                             <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-red-700">Alasan penolakan</p>
                             <p class="mt-1 text-red-800">{{ $booking->refund_rejection_reason }}</p>
                         @endif
                     </div>
                 @endif

                @if ($booking->ticket_number)
                <div class="mb-4 rounded-lg border-2 border-dashed border-[#3F7D6C] bg-emerald-50 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium uppercase tracking-wide text-emerald-700">Nomor Tiket</p>
                            <p class="mt-0.5 font-[IBM_Plex_Mono] text-lg font-bold text-emerald-900">{{ $booking->ticket_number }}</p>
                        </div>
                        <a href="{{ route('ticket.show', $booking) }}" target="_blank"
                           class="rounded-lg bg-[#3F7D6C] px-3 py-1.5 text-sm font-medium text-white transition hover:bg-emerald-600">
                            Lihat Tiket
                        </a>
                    </div>
                 </div>
                 @else
                 <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                     @if ($booking->payment_status !== 'paid')
                         Tiket belum dapat dibuat karena pembayaran belum lunas.
                     @else
                         Tiket belum dibuat. Pastikan kendaraan sudah ditugaskan sebelum membuat tiket.
                     @endif
                 </div>
                @endif

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Layanan</p>
                        <div class="mt-1 flex items-center gap-1.5">
                            <span class="rounded-full px-3 py-0.5 text-sm font-semibold {{ $stColor[$booking->service_type] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $stLabels[$booking->service_type] ?? $booking->service_type }}
                            </span>
                            @if ($booking->session)
                            <span class="rounded-full px-3 py-0.5 text-sm font-semibold {{ $snColor[$booking->session] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ $snLabels[$booking->session] ?? $booking->session }}
                            </span>
                            @endif
                        </div>
                    </div>

                     <div>
                         <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Harga</p>
                         <p class="mt-1 font-[IBM_Plex_Mono] text-lg font-semibold text-blue-900">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</p>
                     </div>

                     <div>
                         <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Jumlah Penumpang</p>
                         <p class="mt-1 text-sm text-slate-700">{{ $booking->jumlah_penumpang }} orang</p>
                     </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Pelanggan</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $booking->pelanggan?->name ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Sopir</p>
                        <p class="mt-1 text-sm text-slate-700">
                            @if ($booking->sopir)
                                {{ $booking->sopir->name }}
                            @elseif ($booking->service_type === 'rental' && !$booking->with_driver)
                                <span class="text-slate-400">Tanpa Sopir (Lepas Kunci)</span>
                            @else
                                -
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Unit</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $booking->vehicle?->nama ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Nomor Plat</p>
                        <p class="mt-1 font-[IBM_Plex_Mono] text-sm text-slate-700">{{ $booking->vehicle?->plat_nomor ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Tanggal Mulai</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $booking->tanggal_mulai }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Tanggal Selesai</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $booking->tanggal_selesai }}</p>
                    </div>

                    @if ($booking->origin || $booking->destination)
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Asal / Tujuan</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $booking->origin ?: '-' }} → {{ $booking->destination ?: '-' }}</p>
                    </div>
                    @endif

                    @if ($booking->flight_number)
                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Nomor Penerbangan</p>
                        <p class="mt-1 font-[IBM_Plex_Mono] text-sm text-slate-700">{{ $booking->flight_number }}</p>
                    </div>
                    @endif

                    @if ($booking->notes)
                    <div class="sm:col-span-2">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Catatan</p>
                        <p class="mt-1 text-sm text-slate-700">{{ $booking->notes }}</p>
                    </div>
                    @endif

                    @if ($booking->service_type === 'travel' && $booking->passengers->count())
                    <div class="sm:col-span-2">
                        <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Data Penumpang</p>
                        <ul class="mt-1 space-y-1 text-sm text-slate-700">
                            @foreach ($booking->passengers as $passenger)
                            <li>{{ $loop->iteration }}. {{ $passenger->nama }} <span class="text-slate-400">({{ $passenger->no_hp }})</span></li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>

                @if (!in_array($booking->status, ['completed', 'cancelled']))
                <hr class="my-6 border-slate-200">

                 <div class="space-y-4">
                     @if (!$booking->sopir_id)
                     <form action="{{ route('admin.bookings.assign-driver', $booking) }}" method="POST" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                         @csrf
                         <p class="mb-3 text-sm font-semibold text-slate-700">Assign Sopir Langsung</p>
                         <div class="flex flex-wrap items-end gap-3">
                             <select name="sopir_id" class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm" required>
                                 <option value="">Pilih Sopir</option>
                                 @foreach ($drivers as $driver)
                                     <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                 @endforeach
                             </select>
                             <button class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white">Assign</button>
                         </div>
                     </form>
                     @endif

                     @if (!$booking->vehicle_id)
                    <form action="{{ route('admin.bookings.assign-vehicle', $booking) }}" method="POST" class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        @csrf
                        <p class="mb-3 text-sm font-semibold text-slate-700">Assign Kendaraan & Sopir</p>
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="flex-1">
                                <select name="vehicle_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-[#E8A33D] focus:ring-[#E8A33D]" required>
                                    <option value="">Pilih Kendaraan</option>
                                    @foreach ($vehicles as $v)
                                        <option value="{{ $v->id }}">{{ $v->nama }} — {{ $v->plat_nomor }} ({{ $v->sopir?->name ?? 'tanpa sopir' }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="rounded-lg bg-[#E8A33D] px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-500 focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">Assign</button>
                        </div>
                    </form>
                    @endif

                     @if ($booking->vehicle_id && !$booking->ticket_number && $booking->payment_status === 'paid' && $booking->payment_scheme !== 'dp')
                    <form action="{{ route('admin.bookings.generate-ticket', $booking) }}" method="POST">
                        @csrf
                        <button class="w-full rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-900">
                            Buat Tiket
                        </button>
                    </form>
                     @endif

                     @if ($booking->payment_status === 'paid' && $booking->payment_scheme === 'dp' && !in_array($booking->status, ['completed', 'cancelled'], true))
                    <form action="{{ route('admin.bookings.mark-paid', $booking) }}" method="POST" onsubmit="return confirm('Konfirmasi pelunasan sisa 70% sudah diterima manual?')">
                        @csrf
                        <button class="w-full rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600">
                            Tandai Lunas (Sisa 70% Manual)
                        </button>
                    </form>
                     @endif

                      @if ($booking->refund_status === 'requested')
                          <div class="rounded-lg border border-orange-200 bg-orange-50 p-4">
                              <p class="mb-3 text-sm font-semibold text-orange-900">Review Pengajuan Refund</p>
                              <div class="flex flex-wrap gap-2">
                                  <form action="{{ route('admin.bookings.refund.approve', $booking) }}" method="POST" class="flex-1" onsubmit="return confirm('Setujui refund dan ajukan ke Midtrans?')">
                                      @csrf
                                      <button class="w-full rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Setujui & Proses</button>
                                  </form>
                                  <form action="{{ route('admin.bookings.refund.reject', $booking) }}" method="POST" class="flex-1" onsubmit="return confirm('Tolak pengajuan refund ini?')">
                                      @csrf
                                      <input type="text" name="refund_rejection_reason" minlength="5" maxlength="1000" required placeholder="Alasan penolakan" class="mb-2 w-full rounded-lg border border-red-200 px-3 py-2 text-xs">
                                      <button class="w-full rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">Tolak Pengajuan</button>
                                  </form>
                              </div>
                          </div>
                      @endif

                      @if ($booking->payment_status === 'paid' && !in_array($booking->status, ['completed', 'cancelled'], true) && in_array($booking->refund_status, ['none', 'failed'], true))
                      <form action="{{ route('admin.bookings.refund', $booking) }}" method="POST" onsubmit="return confirm('Ajukan refund penuh untuk booking ini?')">
                          @csrf
                          <button class="w-full rounded-lg border border-red-200 px-5 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">Refund Langsung via Midtrans</button>
                      </form>
                      @endif

                      @if (in_array($booking->payment_status, ['unpaid', 'failed', 'expired'], true))
                      <form action="{{ route('admin.bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Batalkan booking ini? Customer akan dinotifikasi.')">
                          @csrf
                          <button class="w-full rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700">Batalkan Booking</button>
                      </form>
                      @endif
                 </div>
                @endif

                <div class="mt-6 flex justify-end">
                    <button
                        x-on:click="$dispatch('close')"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-300"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </x-modal>
    @endforeach
@endsection
