@extends('layouts.customer')

@section('content')
@php
    $statusLabels = [
        'pending' => ['label' => 'Menunggu', 'class' => 'bg-amber-100 text-amber-700'],
        'sopir_assigned' => ['label' => 'Sopir Ditugaskan', 'class' => 'bg-blue-100 text-blue-700'],
        'completed' => ['label' => 'Selesai', 'class' => 'bg-green-100 text-green-700'],
        'cancelled' => ['label' => 'Dibatalkan', 'class' => 'bg-red-100 text-red-700'],
    ];
    $paymentLabels = [
        'unpaid' => 'Belum Dibayar',
        'pending' => 'Menunggu Pembayaran',
        'paid' => 'Lunas',
        'failed' => 'Pembayaran Gagal',
        'expired' => 'Pembayaran Kedaluwarsa',
    ];
    $paymentClass = $booking->payment_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700';
    $layanan = $booking->service_type === 'travel'
        ? ['label' => 'Travel', 'class' => 'bg-purple-100 text-purple-700']
        : ['label' => 'Rental', 'class' => 'bg-blue-100 text-blue-700'];
    $s = $statusLabels[$booking->status] ?? ['label' => $booking->status, 'class' => 'bg-slate-100 text-slate-700'];

    $dep = $booking->departed();
    $steps = [
        ['label' => 'Booking Dibuat', 'done' => true, 'time' => $booking->created_at?->translatedFormat('d M Y, H:i')],
        ['label' => 'Pembayaran Diterima', 'done' => $booking->payment_status === 'paid', 'time' => $booking->payment_paid_at?->translatedFormat('d M Y, H:i')],
        ['label' => 'Sopir Ditugaskan', 'done' => in_array($booking->status, ['sopir_assigned', 'completed'], true), 'time' => null],
        ['label' => 'Tiket Dibuat', 'done' => (bool) $booking->ticket_number, 'time' => null],
    ];
    if ($booking->service_type === 'travel') {
        $steps[] = ['label' => 'Kendaraan Berangkat', 'done' => (bool) $dep, 'time' => $dep?->departed_at?->translatedFormat('d M Y, H:i')];
    }
    $steps[] = ['label' => $booking->status === 'cancelled' ? 'Dibatalkan' : 'Selesai', 'done' => in_array($booking->status, ['completed', 'cancelled'], true), 'time' => null];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-slate-900">Detail Booking #{{ $booking->id }}</h1>
            <p class="mt-1 text-sm text-slate-500">Informasi lengkap pemesanan Anda.</p>
        </div>
        <a href="{{ route('bookings.index') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">Kembali ke Riwayat</a>
    </div>

    @if (session('success'))
        <div class="rounded-xl bg-green-100 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <section class="grid gap-6 xl:grid-cols-[1.4fr_1fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex flex-wrap items-center gap-2">
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $layanan['class'] }}">{{ $layanan['label'] }}</span>
                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $s['class'] }}">{{ $s['label'] }}</span>
                @if ($booking->session)
                    <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-semibold text-sky-700">{{ $booking->session === 'pagi' ? 'Pagi (08:00)' : 'Siang (12:00)' }}</span>
                @endif
                @if ($dep)
                    <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-semibold text-teal-700">Sudah Berangkat</span>
                @endif
            </div>

            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Kendaraan</dt>
                    <dd class="mt-1 text-sm font-medium text-slate-800">{{ $booking->vehicle?->nama ?? '-' }}</dd>
                    <dd class="text-xs text-slate-500">{{ $booking->vehicle?->plat_nomor ?? '' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Sopir</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $booking->sopir?->name ?? 'Belum ditugaskan' }}</dd>
                </div>
                @if ($booking->service_type === 'travel')
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Rute</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $booking->origin }} → {{ $booking->destination }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Jumlah Penumpang</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $booking->jumlah_penumpang }} orang</dd>
                </div>
                @endif
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Tanggal Mulai</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ \Carbon\Carbon::parse($booking->tanggal_mulai)->translatedFormat('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Tanggal Selesai</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ \Carbon\Carbon::parse($booking->tanggal_selesai)->translatedFormat('d M Y') }}</dd>
                </div>
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Catatan</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $booking->notes ?: '-' }}</dd>
                </div>
                @if ($booking->service_type === 'travel' && $booking->passengers->count())
                <div class="sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-400">Data Penumpang</dt>
                    <dd class="mt-1">
                        <ol class="space-y-1.5">
                            @foreach ($booking->passengers as $passenger)
                            <li class="flex items-center gap-2 text-sm">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-500">{{ $loop->iteration }}</span>
                                <span class="font-medium text-slate-700">{{ $passenger->nama }}</span>
                                <span class="text-slate-400">{{ $passenger->no_hp }}</span>
                            </li>
                            @endforeach
                        </ol>
                    </dd>
                </div>
                @endif
            </dl>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Pembayaran</h2>
                <div class="mt-3 space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Status</span>
                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $paymentClass }}">{{ $paymentLabels[$booking->payment_status] ?? $booking->payment_status }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Skema</span>
                        <span class="font-medium text-slate-700">{{ $booking->payment_scheme === 'dp' ? 'DP 30%' : 'Lunas Penuh' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500">Nominal Dibayar</span>
                        <span class="font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($booking->payment_amount ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex items-center justify-between border-t border-slate-100 pt-3">
                        <span class="text-slate-500">Total Harga</span>
                        <span class="font-[IBM_Plex_Mono] font-semibold text-blue-900">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Progres Pesanan</h2>
                <ol class="mt-4 space-y-4">
                    @foreach ($steps as $step)
                    <li class="flex items-start gap-3">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold {{ $step['done'] ? 'bg-[#3F7D6C] text-white' : 'bg-slate-100 text-slate-400' }}">{{ $step['done'] ? '✓' : '' }}</span>
                        <div>
                            <p class="text-sm font-medium {{ $step['done'] ? 'text-slate-800' : 'text-slate-400' }}">{{ $step['label'] }}</p>
                            @if ($step['time'])
                                <p class="text-xs text-slate-400">{{ $step['time'] }}</p>
                            @endif
                        </div>
                    </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Aksi</h2>
        <div class="mt-4 flex flex-wrap items-center gap-3">
            @if (in_array($booking->payment_status, ['unpaid', 'failed', 'expired'], true))
                <a href="{{ route('payments.show', $booking) }}" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-800">Bayar Sekarang</a>
                <form action="{{ route('bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Batalkan booking ini?')">
                    @csrf
                    <button class="rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50">Batalkan Booking</button>
                </form>
            @elseif ($booking->payment_status === 'pending')
                <a href="{{ route('payments.show', $booking) }}" class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-800">
                    {{ $booking->payment_token && $booking->payment_expired_at?->isFuture() ? 'Lanjutkan Pembayaran' : 'Buka Pembayaran' }}
                </a>
            @endif

            @if ($booking->ticket_number)
                <a href="{{ route('ticket.show', $booking) }}" class="rounded-lg border border-[#3F7D6C] px-4 py-2 text-sm font-medium text-[#3F7D6C] transition hover:bg-[#3F7D6C] hover:text-white">Lihat Tiket</a>
            @endif

            @if ($booking->payment_status === 'paid')
                <a href="{{ route('bookings.invoice', $booking) }}" target="_blank" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100">Cetak Invoice</a>
            @endif

            @if (in_array($booking->status, ['pending', 'sopir_assigned'], true) && $booking->payment_status === 'paid' && $booking->refund_status === 'none' && ! $dep)
                <details class="text-left">
                    <summary class="cursor-pointer rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600">Ajukan Refund</summary>
                    <form action="{{ route('bookings.refund.request', $booking) }}" method="POST" class="mt-2 w-80 space-y-2 rounded-lg border border-red-100 bg-red-50 p-3">
                        @csrf
                        <textarea name="refund_reason" rows="3" minlength="10" maxlength="1000" required class="w-full rounded-lg border border-red-200 px-2 py-1.5 text-xs text-slate-700" placeholder="Jelaskan alasan refund (minimal 10 karakter)"></textarea>
                        <button type="submit" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Kirim Pengajuan</button>
                    </form>
                </details>
            @endif

            @if ($booking->status === 'completed')
                @if ($booking->review)
                    <span class="inline-flex items-center gap-1 text-sm font-medium text-[#E8A33D]">
                        <span>{{ str_repeat('★', $booking->review->rating) }}</span><span class="text-slate-300">{{ str_repeat('★', 5 - $booking->review->rating) }}</span>
                    </span>
                    @if ($booking->review->komentar)
                        <p class="w-full text-xs text-slate-500">{{ $booking->review->komentar }}</p>
                    @endif
                @else
                    <details class="text-left">
                        <summary class="cursor-pointer rounded-lg border border-[#E8A33D] px-4 py-2 text-sm font-medium text-[#B45309]">Beri Ulasan</summary>
                        <form action="{{ route('bookings.review', $booking) }}" method="POST" class="mt-2 w-80 space-y-2 rounded-lg border border-amber-200 bg-amber-50 p-3">
                            @csrf
                            <div class="flex items-center gap-1">
                                @for ($i = 1; $i <= 5; $i++)
                                <label class="cursor-pointer">
                                    <input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" required>
                                    <span class="text-xl text-slate-300 transition peer-checked:text-[#E8A33D]">★</span>
                                </label>
                                @endfor
                            </div>
                            <textarea name="komentar" rows="2" maxlength="1000" class="w-full rounded-lg border border-amber-200 px-2 py-1.5 text-xs text-slate-700" placeholder="Komentar (opsional, maksimal 1000 karakter)"></textarea>
                            <button type="submit" class="rounded-lg bg-[#E8A33D] px-3 py-1.5 text-xs font-semibold text-blue-900 transition hover:opacity-90">Kirim Ulasan</button>
                        </form>
                    </details>
                @endif
            @endif

            @if ($booking->status === 'cancelled')
                <span class="text-xs text-slate-400">Booking ini sudah dibatalkan.</span>
            @endif
        </div>
    </section>
</div>
@endsection
