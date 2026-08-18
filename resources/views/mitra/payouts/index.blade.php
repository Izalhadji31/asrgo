@extends('layouts.mitra')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Pendapatan Saya</h1>
            <p class="text-sm text-slate-500">Riwayat payout dari booking selesai yang menggunakan armada Anda.</p>
        </div>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Total Pendapatan</p>
            <p class="mt-2 font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">Rp {{ number_format($stats['total'], 0, ',', '.') }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ $stats['count'] }} transaksi payout</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Belum Dicairkan</p>
            <p class="mt-2 font-[IBM_Plex_Mono] text-2xl font-semibold text-amber-600">Rp {{ number_format($stats['pending'], 0, ',', '.') }}</p>
            <p class="mt-2 text-sm text-slate-500">Menunggu konfirmasi admin</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Sudah Dicairkan</p>
            <p class="mt-2 font-[IBM_Plex_Mono] text-2xl font-semibold text-emerald-600">Rp {{ number_format($stats['paid'], 0, ',', '.') }}</p>
            <p class="mt-2 text-sm text-slate-500">Payout lunas</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Bagi Hasil</p>
            <p class="mt-2 font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-800">Mitra 80%</p>
            <p class="mt-2 text-sm text-slate-500">Skema default platform 20% / mitra 80%</p>
        </article>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Performa Unit</h2>
        <p class="text-sm text-slate-500">Pendapatan per kendaraan dari booking selesai — urut dari yang tertinggi.</p>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Unit</th>
                        <th class="px-5 py-3 font-medium">Plat Nomor</th>
                        <th class="px-5 py-3 font-medium">Booking Selesai</th>
                        <th class="px-5 py-3 font-medium">Total Pendapatan</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($unitStats as $unit)
                        <tr class="odd:bg-white even:bg-slate-50/50">
                            <td class="px-5 py-4 font-medium text-slate-800">{{ $unit['nama'] }}</td>
                            <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-600">{{ $unit['plat'] }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $unit['bookings'] }} booking</td>
                            <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($unit['total'], 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                @if ($unit['status'] === 'tersedia')
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Tersedia</span>
                                @elseif ($unit['status'] === 'disewa')
                                    <span class="rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">Disewa</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">{{ ucfirst($unit['status']) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">Belum ada data unit.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4">
            <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Riwayat Payout</h2>
            <p class="text-sm text-slate-500">Payout terbentuk otomatis saat booking selesai.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Tanggal</th>
                        <th class="px-5 py-3 font-medium">Booking</th>
                        <th class="px-5 py-3 font-medium">Unit</th>
                        <th class="px-5 py-3 font-medium">Nominal Mitra</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($payouts as $payout)
                        <tr class="odd:bg-white even:bg-slate-50/50">
                            <td class="px-5 py-4 text-slate-600">{{ $payout->created_at?->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">#{{ $payout->booking_id }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $payout->booking?->vehicle?->nama ?? '-' }}</td>
                            <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($payout->jumlah_mitra, 0, ',', '.') }}</td>
                            <td class="px-5 py-4">
                                @if ($payout->status_pencairan === 'paid')
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Lunas</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Pending</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">Belum ada payout untuk armada Anda.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $payouts->links() }}
        </div>
    </div>
</div>
@endsection
