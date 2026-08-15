@extends('layouts.customer')

@section('content')
    @php
        $stLabels = ['rental' => 'Rental', 'travel' => 'Travel'];
        $snLabels = ['pagi' => 'Pagi', 'siang' => 'Siang'];
        $ticketDate = \Carbon\Carbon::parse($booking->created_at)->format('d M Y, H:i');
        $statusKey = in_array($booking->status, ['completed', 'cancelled']) ? $booking->status : $booking->ticket_status;
    @endphp

    <div class="mx-auto max-w-lg space-y-6">
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1 text-sm text-slate-500 transition hover:text-slate-700">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M17 10a.75.75 0 01-.75.75H5.612l4.158 3.96a.75.75 0 11-1.04 1.08l-5.5-5.25a.75.75 0 010-1.08l5.5-5.25a.75.75 0 111.04 1.08L5.612 9.25H16.25A.75.75 0 0117 10z" clip-rule="evenodd" />
            </svg>
            Kembali
        </a>

        {{-- TIKET KARTU --}}
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-lg print:shadow-none print:border-none">
            {{-- Header --}}
            <div class="bg-blue-900 px-6 py-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-[Barlow_Condensed] text-2xl font-semibold tracking-wide text-white">ASRGO</p>
                        <p class="mt-0.5 text-xs text-slate-400">Rental Armada</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-slate-400">No. Tiket</p>
                        <p class="font-[IBM_Plex_Mono] text-xl font-bold text-[#E8A33D]">{{ $booking->ticket_number }}</p>
                    </div>
                </div>
            </div>

            {{-- Status Bar --}}
            <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 bg-slate-50 px-6 py-3">
                <span class="rounded-full px-3 py-1 text-xs font-semibold
                    @switch($statusKey)
                        @case('created') bg-blue-100 text-blue-700 @break
                        @case('completed') bg-green-100 text-green-700 @break
                        @case('cancelled') bg-red-100 text-red-700 @break
                        @default bg-amber-100 text-amber-700
                    @endswitch">
                    {{ match($statusKey) {
                        'created' => 'Tiket Dibuat',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        default => 'Belum Dapat Tiket',
                    } }}
                </span>
                <span class="rounded-full px-3 py-1 text-xs font-semibold bg-slate-200 text-slate-700">
                    {{ $stLabels[$booking->service_type] ?? $booking->service_type }}
                </span>
                @if ($booking->session)
                <span class="rounded-full px-3 py-1 text-xs font-semibold bg-slate-200 text-slate-700">
                    {{ $snLabels[$booking->session] ?? $booking->session }}
                </span>
                @endif
                <span class="ml-auto text-xs text-slate-400">{{ $ticketDate }}</span>
            </div>

            {{-- Body --}}
            <div class="p-6">
                {{-- Customer Section --}}
                <div class="mb-5">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-slate-400">Data Penumpang</p>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 text-blue-700 text-sm font-bold">
                                {{ strtoupper(substr($booking->pelanggan?->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-[Barlow_Condensed] text-lg font-semibold text-slate-800">{{ $booking->pelanggan?->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $booking->pelanggan?->email ?? '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Trip Section --}}
                <div class="mb-5">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-slate-400">Detail Perjalanan</p>
                    <div class="rounded-xl border border-slate-200 divide-y divide-slate-100">
                        <div class="grid grid-cols-2 gap-3 p-4">
                            <div>
                                <p class="text-xs text-slate-400">Tanggal Mulai</p>
                                <p class="mt-0.5 font-semibold text-slate-800">{{ \Carbon\Carbon::parse($booking->tanggal_mulai)->translatedFormat('l, d F Y') }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Tanggal Selesai</p>
                                <p class="mt-0.5 font-semibold text-slate-800">{{ \Carbon\Carbon::parse($booking->tanggal_selesai)->translatedFormat('l, d F Y') }}</p>
                            </div>
                        </div>

                        @if ($booking->origin || $booking->destination)
                        <div class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex-1 text-center">
                                    <p class="font-[Barlow_Condensed] text-xl font-semibold text-slate-800">{{ $booking->origin ?: '-' }}</p>
                                    <p class="text-xs text-slate-400">Asal</p>
                                </div>
                                <div class="flex flex-col items-center">
                                    <svg class="h-6 w-6 text-[#E8A33D]" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M2 10a.75.75 0 01.75-.75h12.59l-2.1-1.95a.75.75 0 111.02-1.1l3.5 3.25a.75.75 0 010 1.1l-3.5 3.25a.75.75 0 11-1.02-1.1l2.1-1.95H2.75A.75.75 0 012 10z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="flex-1 text-center">
                                    <p class="font-[Barlow_Condensed] text-xl font-semibold text-slate-800">{{ $booking->destination ?: '-' }}</p>
                                    <p class="text-xs text-slate-400">Tujuan</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if ($booking->flight_number)
                        <div class="p-4">
                            <p class="text-xs text-slate-400">Nomor Penerbangan</p>
                            <p class="mt-0.5 font-[IBM_Plex_Mono] font-semibold text-slate-800">{{ $booking->flight_number }}</p>
                        </div>
                        @endif

                        @if ($booking->service_type === 'travel')
                        <div class="p-4">
                            <p class="text-xs text-slate-400">Jumlah Penumpang</p>
                            <p class="mt-0.5 font-semibold text-slate-800">{{ $booking->jumlah_penumpang }} orang</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Vehicle Section --}}
                <div class="mb-5">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-slate-400">Kendaraan & Sopir</p>
                    <div class="rounded-xl border border-slate-200 divide-y divide-slate-100">
                        <div class="grid grid-cols-2 gap-3 p-4">
                            <div>
                                <p class="text-xs text-slate-400">Unit / Jenis</p>
                                <p class="mt-0.5 font-semibold text-slate-800">{{ $booking->vehicle?->nama ?? '-' }}</p>
                                <p class="text-xs text-slate-500">{{ $booking->vehicle?->jenis ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Nomor Plat</p>
                                <p class="mt-0.5 font-[IBM_Plex_Mono] font-semibold text-slate-800">{{ $booking->vehicle?->plat_nomor ?? '-' }}</p>
                            </div>
                        </div>
                        <div class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-700 text-xs font-bold">
                                    S
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">Sopir Bertugas</p>
                                    @if ($booking->sopir)
                                        <p class="font-semibold text-slate-800">{{ $booking->sopir->name }}</p>
                                    @elseif ($booking->service_type === 'rental' && !$booking->with_driver)
                                        <p class="font-semibold italic text-slate-500">Tanpa Sopir (Lepas Kunci)</p>
                                    @else
                                        <p class="font-semibold text-slate-400">-</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                @if ($booking->notes)
                <div class="mb-5">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-slate-400">Catatan</p>
                    <div class="rounded-xl border border-slate-200 bg-amber-50 p-4">
                        <p class="text-sm text-slate-700">{{ $booking->notes }}</p>
                    </div>
                </div>
                @endif

                {{-- Price & Booking ID --}}
                <div class="grid grid-cols-2 gap-5">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs text-slate-400">Booking ID</p>
                        <p class="mt-0.5 font-[IBM_Plex_Mono] font-semibold text-slate-700">#{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</p>
                    </div>
                    <div class="rounded-xl border border-dashed border-[#E8A33D] bg-amber-50 p-4 text-right">
                        <p class="text-xs font-medium uppercase tracking-wide text-amber-700">Total</p>
                        <p class="mt-0.5 font-[IBM_Plex_Mono] text-xl font-bold text-blue-900">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Footer Barcode --}}
            <div class="border-t-2 border-dashed border-slate-300 px-6 py-5">
                <div class="flex items-center justify-between gap-4">
                    <div class="text-xs text-slate-400">
                        <p>Tiket ini adalah bukti pemesanan resmi.</p>
                        <p>Tunjukkan ke sopir saat perjalanan.</p>
                    </div>
                    <div class="flex-shrink-0 rounded-lg bg-white px-3 py-1.5 border border-slate-300 font-[IBM_Plex_Mono] text-xs tracking-widest text-slate-400">
                        ║ ▌│║▌│║║▌║▌│║
                    </div>
                </div>
            </div>
        </div>

        {{-- Download Button --}}
        <div class="flex justify-center print:hidden">
            <button onclick="window.print()" class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-900 focus:ring-offset-2">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 2.75a.75.75 0 00-1.5 0v8.614L6.295 8.235a.75.75 0 10-1.09 1.03l4.25 4.5a.75.75 0 001.09 0l4.25-4.5a.75.75 0 00-1.09-1.03l-2.955 3.129V2.75z" />
                    <path d="M3.5 12.75a.75.75 0 00-1.5 0v2.5A2.75 2.75 0 004.75 18h10.5A2.75 2.75 0 0018 15.25v-2.5a.75.75 0 00-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5z" />
                </svg>
                Download Tiket
            </button>
        </div>
    </div>
@endsection
