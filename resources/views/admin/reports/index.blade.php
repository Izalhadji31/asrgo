@extends('layouts.admin')

@section('content')
    @php
        $summaryCards = [
            [
                'label' => 'Pendapatan Kotor',
                'value' => 'Rp ' . number_format($stats['gross'], 0, ',', '.'),
                'note' => 'Total dari seluruh payout yang terbentuk',
                'accent' => 'bg-blue-900',
            ],
            [
                'label' => 'Pendapatan Platform',
                'value' => 'Rp ' . number_format($stats['platform'], 0, ',', '.'),
                'note' => 'Bagian platform dari semua transaksi',
                'accent' => 'bg-[#3F7D6C]',
            ],
            [
                'label' => 'Pendapatan Mitra',
                'value' => 'Rp ' . number_format($stats['mitra'], 0, ',', '.'),
                'note' => 'Akumulasi bagian mitra',
                'accent' => 'bg-[#E8A33D]',
            ],
            [
                'label' => 'Pencairan Pending',
                'value' => 'Rp ' . number_format($stats['pending'], 0, ',', '.'),
                'note' => 'Total nominal payout yang belum dibayar',
                'accent' => 'bg-[#C1443C]',
            ],
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Laporan Keuangan</h1>
                <p class="text-sm text-slate-500">Ringkasan pendapatan, payout, dan status pencairan sistem.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">Kembali ke Ringkasan</a>
        </div>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($summaryCards as $card)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <span class="h-3 w-3 rounded-full {{ $card['accent'] }}"></span>
                    </div>
                    <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">{{ $card['value'] }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $card['note'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between gap-3">
                    <div>
                        <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Status Pencairan</h2>
                        <p class="text-sm text-slate-500">Jumlah transaksi berdasarkan status pencairan.</p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Pending</p>
                        <p class="mt-2 font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">{{ $statusBreakdown['pending'] }}</p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Paid</p>
                        <p class="mt-2 font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">{{ $statusBreakdown['paid'] }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-xl border border-dashed border-slate-300 p-4 text-sm text-slate-600">
                    <p class="font-semibold text-slate-700">Split default aktif</p>
                    <p class="mt-1">
                        Platform {{ $globalShare?->persen_platform ?? 0 }}% dan Mitra {{ $globalShare?->persen_mitra ?? 0 }}%.
                    </p>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4">
                    <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Rekap Bulan Ini</h2>
                    <p class="text-sm text-slate-500">Pendapatan yang tercipta pada bulan berjalan.</p>
                </div>

                <div class="space-y-4">
                    <div class="rounded-xl bg-slate-50 p-4">
                        <p class="text-sm text-slate-500">Gross Bulan Ini</p>
                        <p class="mt-2 font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">Rp {{ number_format($stats['monthly_gross'], 0, ',', '.') }}</p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Platform</p>
                            <p class="mt-2 font-[IBM_Plex_Mono] text-xl font-semibold text-blue-900">Rp {{ number_format($stats['monthly_platform'], 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-sm text-slate-500">Mitra</p>
                            <p class="mt-2 font-[IBM_Plex_Mono] text-xl font-semibold text-blue-900">Rp {{ number_format($stats['monthly_mitra'], 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Riwayat Payout</h2>
                <p class="text-sm text-slate-500">Transaksi payout terbaru yang terbentuk dari booking selesai.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Booking</th>
                            <th class="px-5 py-3 font-medium">Mitra</th>
                            <th class="px-5 py-3 font-medium">Customer</th>
                            <th class="px-5 py-3 font-medium">Unit</th>
                            <th class="px-5 py-3 font-medium">Platform</th>
                            <th class="px-5 py-3 font-medium">Mitra</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentPayouts as $payout)
                            <tr class="odd:bg-white even:bg-slate-50/50">
                                <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">#{{ $payout->booking_id }}</td>
                                <td class="px-5 py-4 text-slate-700">
                                    <p class="font-medium">{{ $payout->mitra?->name ?? '-' }}</p>
                                    <p class="text-xs text-slate-500">{{ $payout->mitra?->email ?? '-' }}</p>
                                </td>
                                <td class="px-5 py-4 text-slate-700">{{ $payout->booking?->pelanggan?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-700">{{ $payout->booking?->vehicle?->nama ?? '-' }}</td>
                                <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($payout->jumlah_platform, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($payout->jumlah_mitra, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    @if ($payout->status_pencairan === 'paid')
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Paid</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Pending</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($payout->status_pencairan === 'pending')
                                        <form action="{{ route('admin.payouts.pay', $payout) }}" method="POST" onsubmit="return confirm('Tandai lunas payout ini?')">
                                            @csrf
                                            <button class="rounded-lg bg-[#3F7D6C] px-3 py-1 text-xs font-medium text-white transition hover:opacity-90">Lunas</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada payout yang terbentuk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $recentPayouts->links() }}
            </div>
        </div>
    </div>
@endsection
