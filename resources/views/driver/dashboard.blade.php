@extends('layouts.driver')

@section('content')
    @php
        $statusStyles = [
            'not_created'    => ['label' => 'Belum Dapat Tiket', 'class' => 'bg-amber-100 text-amber-700'],
            'created'        => ['label' => 'Tiket Dibuat', 'class' => 'bg-blue-100 text-blue-700'],
            'completed'      => ['label' => 'Selesai', 'class' => 'bg-green-100 text-green-700'],
            'cancelled'      => ['label' => 'Dibatalkan', 'class' => 'bg-red-100 text-red-700'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-slate-900">Dashboard Sopir</h1>
            <p class="mt-1 text-sm text-slate-500">Pantau jadwal tugas dan status booking Anda hari ini.</p>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Tugas Hari Ini</p>
                    <span class="h-3 w-3 rounded-full bg-indigo-600"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $todayTasks->count() }}</p>
                <p class="mt-2 text-sm text-slate-500">Booking aktif hari ini</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Tugas Aktif</p>
                    <span class="h-3 w-3 rounded-full bg-indigo-500"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $assignedCount }}</p>
                <p class="mt-2 text-sm text-slate-500">Booking yang menunggu</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Selesai Hari Ini</p>
                    <span class="h-3 w-3 rounded-full bg-green-600"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $completedToday }}</p>
                <p class="mt-2 text-sm text-slate-500">Booking diselesaikan</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Total Bulan Ini</p>
                    <span class="h-3 w-3 rounded-full bg-amber-500"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $completedThisMonth }}</p>
                <p class="mt-2 text-sm text-slate-500">Booking selesai bulan ini</p>
            </article>
        </section>

        @if (session('success'))
            <div class="rounded-xl bg-green-100 p-4 text-sm text-green-700">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="rounded-xl bg-blue-100 p-4 text-sm text-blue-700">{{ session('info') }}</div>
        @endif
        @if ($errors->any())
            <div class="rounded-xl bg-red-100 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-slate-900">Keberangkatan Travel Hari Ini</h2>
                <p class="text-sm text-slate-500">Tandai kendaraan berangkat agar booking baru dialihkan ke kendaraan berikutnya.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Rute</th>
                            <th class="px-5 py-3 font-medium">Sesi</th>
                            <th class="px-5 py-3 font-medium">Kendaraan</th>
                            <th class="px-5 py-3 font-medium">Penumpang</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($departureAssignments as $assignment)
                            @foreach ($assignment->mitra->vehicles as $vehicle)
                                @php
                                    $departure = $assignment->departures->firstWhere('vehicle_id', $vehicle->id);
                                    $passengerKey = "{$assignment->route_id}:{$assignment->session}:{$vehicle->id}";
                                    $passengerCount = $passengerCounts[$passengerKey] ?? 0;
                                @endphp
                                <tr class="odd:bg-white even:bg-slate-50/50">
                                    <td class="px-5 py-4 text-slate-700">{{ $assignment->route->origin }} → {{ $assignment->route->destination }}</td>
                                    <td class="px-5 py-4 capitalize text-slate-600">{{ $assignment->session }}</td>
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-slate-700">{{ $vehicle->nama }}</p>
                                        <p class="font-[IBM_Plex_Mono] text-xs text-slate-500">{{ $vehicle->plat_nomor }} · {{ $vehicle->kapasitas_penumpang }} seat</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $passengerCount }} / {{ $vehicle->kapasitas_penumpang }}</td>
                                    <td class="px-5 py-4">
                                        @if ($departure)
                                            <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-semibold text-slate-600">Sudah Berangkat</span>
                                        @else
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Belum Berangkat</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if (!$departure)
                                            <form action="{{ route('driver.route-assignments.depart', [$assignment, $vehicle]) }}" method="POST" onsubmit="return confirm('Tandai kendaraan ini sudah berangkat?')">
                                                @csrf
                                                <button class="rounded-lg bg-blue-900 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-blue-800">Berangkat</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">Tercatat</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada penugasan travel untuk kendaraan Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-slate-900">Jadwal Tugas Mendatang</h2>
                <p class="text-sm text-slate-500">Booking hari ini dan booking yang sudah masuk untuk tanggal berikutnya.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Kendaraan</th>
                            <th class="px-5 py-3 font-medium">Plat</th>
                            <th class="px-5 py-3 font-medium">Tanggal Mulai</th>
                            <th class="px-5 py-3 font-medium">Tanggal Selesai</th>
                            <th class="px-5 py-3 font-medium">Penumpang</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($upcomingTasks as $task)
                            @php $statusKey = in_array($task->status, ['completed', 'cancelled']) ? $task->status : $task->ticket_status;
                               $s = $statusStyles[$statusKey] ?? ['label' => $statusKey, 'class' => 'bg-slate-100 text-slate-700']; @endphp
                            <tr class="odd:bg-white even:bg-slate-50/50">
                                <td class="px-5 py-4 font-medium text-slate-700">{{ $task->vehicle?->nama ?? '-' }}</td>
                                <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-600">{{ $task->vehicle?->plat_nomor ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ \Carbon\Carbon::parse($task->tanggal_mulai)->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ \Carbon\Carbon::parse($task->tanggal_selesai)->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $task->service_type === 'travel' ? $task->jumlah_penumpang . ' orang' : '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $s['class'] }}">{{ $s['label'] }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        @if (\Carbon\Carbon::parse($task->tanggal_mulai)->isFuture())
                                            <span class="text-xs text-slate-400">Menunggu tanggal</span>
                                        @elseif ($task->status !== 'completed' && $task->status !== 'cancelled')
                                        <form action="{{ route('bookings.complete', $task) }}" method="POST">
                                            @csrf
                                            <button class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-green-700">Selesai</button>
                                        </form>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-10 w-10 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                        </svg>
                                        <p class="text-sm font-medium text-slate-500">Tidak ada tugas untuk hari ini</p>
                                        <p class="text-xs text-slate-400">Santai dulu, bang.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
