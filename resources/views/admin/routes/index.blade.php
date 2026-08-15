@extends('layouts.admin')

@section('content')
    @php $total = $routes->count(); @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Kelola Rute</h1>
                <p class="text-sm text-slate-500">Atur rute, harga, dan mitra keberangkatan untuk layanan Travel.</p>
            </div>
            <a href="{{ route('admin.routes.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#3F7D6C] px-4 py-2.5 text-sm font-medium text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#3F7D6C] focus:ring-offset-2">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Tambah Rute
            </a>
        </div>

        <section>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm max-w-xs">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Total Rute</p>
                    <span class="h-3 w-3 rounded-full bg-purple-500"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">{{ $total }}</p>
            </article>
        </section>

        @if (session('success'))
            <div class="rounded-lg bg-green-100 p-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Layanan</th>
                            <th class="px-5 py-3 font-medium">Asal</th>
                            <th class="px-5 py-3 font-medium">Tujuan</th>
                            <th class="px-5 py-3 font-medium">Harga</th>
                            <th class="px-5 py-3 font-medium">Mitra Pagi (08:00-12:00)</th>
                            <th class="px-5 py-3 font-medium">Mitra Siang (12:00-17:00)</th>
                            <th class="px-5 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($routes as $route)
                            @php
                                 $assignmentPagi = $route->assignments->firstWhere('session', 'pagi');
                                 $assignmentSiang = $route->assignments->firstWhere('session', 'siang');
                                $stLabels = ['travel' => 'Travel'];
                                $stColors = ['travel' => 'bg-purple-100 text-purple-700'];
                            @endphp
                            <tr class="odd:bg-white even:bg-slate-50/50">
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $stColors[$route->service_type] ?? 'bg-slate-100 text-slate-700' }}">
                                        {{ $stLabels[$route->service_type] ?? $route->service_type }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 font-medium text-slate-700">{{ $route->origin }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $route->destination }}</td>
                                <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($route->price, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                     @if ($assignmentPagi?->mitra)
                                         <span class="text-slate-700">{{ $assignmentPagi->mitra->name }}</span>
                                         <span class="ml-1 text-xs text-slate-400">{{ $assignmentPagi->mitra->vehicles->count() }} kendaraan</span>
                                     @else
                                         <span class="text-slate-400">—</span>
                                     @endif
                                </td>
                                <td class="px-5 py-4">
                                     @if ($assignmentSiang?->mitra)
                                         <span class="text-slate-700">{{ $assignmentSiang->mitra->name }}</span>
                                         <span class="ml-1 text-xs text-slate-400">{{ $assignmentSiang->mitra->vehicles->count() }} kendaraan</span>
                                     @else
                                         <span class="text-slate-400">—</span>
                                     @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('admin.routes.edit', $route) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-100">Edit</a>
                                        <form action="{{ route('admin.routes.destroy', $route) }}" method="POST" onsubmit="return confirm('Hapus rute ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada rute. Tambahkan rute untuk layanan Travel.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
