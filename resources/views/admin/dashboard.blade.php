@extends('layouts.admin')

@section('content')
@php
$stats = [
[
'label' => 'Booking Hari Ini',
'value' => $bookingsToday,
'note' => $bookingsToday > 0 ? 'Booking masuk hari ini' : 'Belum ada booking hari ini',
'accent' => 'bg-[#E8A33D]',
],
[
'label' => 'Unit Tersedia',
'value' => $availableUnits,
'note' => $maintenanceUnits > 0 ? $maintenanceUnits . ' unit maintenance' : 'Tidak ada unit maintenance',
'accent' => 'bg-[#3F7D6C]',
],
[
'label' => 'Total Mitra',
'value' => $totalMitra,
'note' => 'Mitra terdaftar di sistem',
'accent' => 'bg-[#C1443C]',
],
[
'label' => 'Pendapatan Bulan Ini',
'value' => 'Rp ' . number_format($monthlyRevenue, 0, ',', '.'),
'note' => 'Periode ' . now()->translatedFormat('F Y'),
'accent' => 'bg-blue-900',
],
];
@endphp

<div class="space-y-6" x-data="paymentStatusWatcher(@js(route('payments.statuses')))">
    <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                <span class="h-3 w-3 rounded-full {{ $stat['accent'] }}"></span>
            </div>
            <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">{{ $stat['value'] }}</p>
            <p class="mt-2 text-sm text-slate-500">{{ $stat['note'] }}</p>
        </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Tren Booking & Pendapatan</h2>
                <p class="text-sm text-slate-500">6 bulan terakhir berdasarkan tanggal booking.</p>
            </div>
            <div class="h-72">
                <canvas id="chartTrend"></canvas>
            </div>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Booking per Layanan</h2>
                <p class="text-sm text-slate-500">Distribusi layanan travel dan rental.</p>
            </div>
            <div class="flex h-72 items-center justify-center">
                <canvas id="chartServices"></canvas>
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-4">
            <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Status Booking</h2>
            <p class="text-sm text-slate-500">Kondisi seluruh booking berdasarkan status saat ini.</p>
        </div>
        <div class="h-64">
            <canvas id="chartStatus"></canvas>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.4fr_0.9fr]">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                <div>
                    <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Papan Booking</h2>
                    <p class="text-sm text-slate-500">Status aktif dan kebutuhan tindak lanjut.</p>
                </div>
                <a href="{{ route('admin.bookings.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">Lihat Semua</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Status Booking</th>
                            <th class="px-5 py-3 font-medium">Status Tiket</th>
                            <th class="px-5 py-3 font-medium">Nomor Plat</th>
                            <th class="px-5 py-3 font-medium">Pelanggan</th>
                            <th class="px-5 py-3 font-medium">Unit Mobil</th>
                            <th class="px-5 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recentBookings as $booking)
                        <tr class="odd:bg-white even:bg-slate-50/50">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="h-3 w-3 rounded-full {{ $booking['status_class'] }}"></span>
                                    <span class="capitalize text-slate-600">{{ $booking['status'] }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $booking['ticket_status_class'] }}">
                                    {{ $booking['ticket_status'] }}
                                </span>
                            </td>
                            <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">{{ $booking['plate'] }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $booking['customer'] }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $booking['unit'] }}</td>
                            <td class="px-5 py-4">
                                <a href="{{ $booking['action_route'] }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">
                                    {{ $booking['action'] }}
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4">
                    <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Bagi Hasil</h2>
                    <p class="text-sm text-slate-500">Atur persentase default platform vs mitra.</p>
                </div>

                <form action="{{ route('admin.revenue-shares.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="platform" class="mb-1 block text-sm font-medium text-slate-700">Platform</label>
                        <input id="platform" name="persen_platform" type="number" min="0" max="100" value="{{ $revenueShare?->persen_platform ?? 20 }}" class="w-full rounded-lg border border-slate-300 bg-[#F5F4F0] px-3 py-2 text-sm text-slate-800 focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]" />
                    </div>
                    <div>
                        <label for="mitra" class="mb-1 block text-sm font-medium text-slate-700">Mitra</label>
                        <input id="mitra" name="persen_mitra" type="number" min="0" max="100" value="{{ $revenueShare?->persen_mitra ?? 80 }}" class="w-full rounded-lg border border-slate-300 bg-[#F5F4F0] px-3 py-2 text-sm text-slate-800 focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]" />
                    </div>
                    <button type="submit" class="w-full rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-950 focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">Simpan</button>
                </form>
            </section>
        </div>
    </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const chartData = @json($chartData);
    const rp = (v) => 'Rp ' + Number(v).toLocaleString('id-ID');

    const trendCtx = document.getElementById('chartTrend');
    if (trendCtx) {
        new Chart(trendCtx, {
            data: {
                labels: chartData.months,
                datasets: [
                    { type: 'bar', label: 'Jumlah Booking', data: chartData.bookingCounts, backgroundColor: '#1e3a8a', borderRadius: 4, yAxisID: 'y' },
                    { type: 'line', label: 'Pendapatan', data: chartData.revenues, borderColor: '#3F7D6C', backgroundColor: '#3F7D6C', yAxisID: 'y1', tension: 0.3 }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ctx.dataset.label + ': ' + (ctx.dataset.type === 'line' ? rp(ctx.parsed.y) : ctx.parsed.y + ' booking')
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e2e8f0' } },
                    y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { callback: (v) => v >= 1000000 ? (v / 1000000).toFixed(1) + ' jt' : v } }
                }
            }
        });
    }

    const servicesCtx = document.getElementById('chartServices');
    if (servicesCtx) {
        new Chart(servicesCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.services.labels,
                datasets: [{ data: chartData.services.data, backgroundColor: ['#0064d2', '#0e7490'], borderWidth: 2, borderColor: '#ffffff' }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: { callbacks: { label: (ctx) => ctx.label + ': ' + ctx.parsed + ' booking' } }
                }
            }
        });
    }

    const statusCtx = document.getElementById('chartStatus');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: chartData.status.labels,
                datasets: [{ label: 'Jumlah Booking', data: chartData.status.data, backgroundColor: ['#E8A33D', '#3F7D6C', '#2563eb', '#C1443C'], borderRadius: 4 }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: (ctx) => ctx.parsed.x + ' booking' } }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#e2e8f0' } }
                }
            }
        });
    }
});
</script>
@endsection
