@extends('layouts.mitra')

@section('content')
    @php
        $statCards = [
            [
                'label' => 'Total Unit',
                'value' => $totalUnits,
                'note' => 'Seluruh kendaraan terdaftar',
                'accent' => 'bg-[#0F172A]',
            ],
            [
                'label' => 'Unit Aktif',
                'value' => $activeUnits,
                'note' => 'Siap disewa pelanggan',
                'accent' => 'bg-[#3F7D6C]',
            ],
            [
                'label' => 'Disewa',
                'value' => $rentedUnits,
                'note' => 'Sedang dalam pemakaian',
                'accent' => 'bg-[#2563EB]',
            ],
            [
                'label' => 'Pendapatan Bulan Ini',
                'value' => 'Rp ' . number_format($monthlyIncome, 0, ',', '.'),
                'note' => 'Dari booking selesai',
                'accent' => 'bg-[#E8A33D]',
            ],
        ];

        $statusStyles = [
            'not_created'    => ['label' => 'Belum Dapat Tiket', 'class' => 'bg-amber-100 text-amber-700'],
            'created'        => ['label' => 'Tiket Dibuat', 'class' => 'bg-blue-100 text-blue-700'],
            'completed'      => ['label' => 'Selesai', 'class' => 'bg-green-100 text-green-700'],
            'cancelled'      => ['label' => 'Dibatalkan', 'class' => 'bg-red-100 text-red-700'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-slate-900">Dashboard Mitra</h1>
                <p class="text-sm text-slate-500">Pantau performa armada dan pendapatan Anda secara real-time.</p>
            </div>
            <a href="{{ route('vehicles.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#3F7D6C] px-4 py-2.5 text-sm font-medium text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#3F7D6C] focus:ring-offset-2">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Tambah Unit
            </a>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($statCards as $stat)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                        <span class="h-3 w-3 rounded-full {{ $stat['accent'] }}"></span>
                    </div>
                    <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $stat['note'] }}</p>
                </article>
            @endforeach
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-slate-900">Booking Terbaru</h2>
                    <p class="text-sm text-slate-500">5 transaksi terakhir pada armada Anda.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Layanan</th>
                            <th class="px-5 py-3 font-medium">Unit</th>
                            <th class="px-5 py-3 font-medium">Pelanggan</th>
                                <th class="px-5 py-3 font-medium">Total</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($recentBookings as $booking)
                                @php $statusKey = in_array($booking->status, ['completed', 'cancelled']) ? $booking->status : $booking->ticket_status;
                                $s = $statusStyles[$statusKey] ?? ['label' => $statusKey, 'class' => 'bg-slate-100 text-slate-700'];
                                $stLabels = ['rental' => 'Rental', 'travel' => 'Travel'];
                                $stColor = ['rental' => 'bg-blue-100 text-blue-700', 'travel' => 'bg-purple-100 text-purple-700'];
                                $snLabels = ['pagi' => 'Pagi', 'siang' => 'Siang'];
                                $snColor = ['pagi' => 'bg-sky-100 text-sky-700', 'siang' => 'bg-orange-100 text-orange-700']; @endphp
                                <tr>
                                    <td class="px-5 py-3">
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
                                    <td class="px-5 py-3 font-medium text-slate-700">{{ $booking->vehicle?->nama ?? '-' }}</td>
                                    <td class="px-5 py-3 text-slate-600">{{ $booking->pelanggan?->name ?? '-' }}</td>
                                    <td class="px-5 py-3 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $s['class'] }}">{{ $s['label'] }}</span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-500">{{ $booking->created_at?->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada transaksi booking.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h3 class="font-[Barlow_Condensed] text-xl font-semibold text-slate-900">Status Unit</h3>
                    <div class="mt-4 space-y-3">
                        @php
                            $statusSummary = [
                                ['label' => 'Tersedia', 'count' => $activeUnits, 'color' => 'bg-[#3F7D6C]'],
                                ['label' => 'Disewa', 'count' => $rentedUnits, 'color' => 'bg-[#2563EB]'],
                                ['label' => 'Maintenance', 'count' => $maintenanceUnits, 'color' => 'bg-[#E8A33D]'],
                            ];
                        @endphp
                        @foreach ($statusSummary as $item)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="h-2.5 w-2.5 rounded-full {{ $item['color'] }}"></span>
                                    <span class="text-sm text-slate-600">{{ $item['label'] }}</span>
                                </div>
                                <span class="font-[IBM_Plex_Mono] text-sm font-semibold text-slate-900">{{ $item['count'] }}</span>
                            </div>
                        @endforeach
                        @if ($totalUnits > 0)
                            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                                @php $pctTersedia = $totalUnits > 0 ? ($activeUnits / $totalUnits) * 100 : 0; @endphp
                                <div class="h-full rounded-full bg-[#3F7D6C]" style="width: {{ $pctTersedia }}%"></div>
                            </div>
                            <p class="text-xs text-slate-400">{{ round($pctTersedia) }}% unit tersedia</p>
                        @endif
                    </div>
                </div>

                @if ($pendingUnits > 0)
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                    <div class="flex items-center gap-2">
                        <svg class="h-5 w-5 text-amber-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
                        </svg>
                        <h3 class="text-sm font-semibold text-amber-800">Menunggu Approval</h3>
                    </div>
                    <p class="mt-1 text-sm text-amber-700">{{ $pendingUnits }} unit kendaraan masih menunggu persetujuan admin.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection
