@extends('layouts.admin')

@section('content')
    @php
        $statusStyles = [
            'tersedia' => ['label' => 'Tersedia', 'class' => 'bg-green-100 text-green-700'],
            'disewa' => ['label' => 'Disewa', 'class' => 'bg-blue-100 text-blue-700'],
            'maintenance' => ['label' => 'Maintenance', 'class' => 'bg-amber-100 text-amber-700'],
        ];
    @endphp

    <div class="space-y-6" x-data="{ selectedMitraId: 'all' }">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Kelola Armada</h1>
                <p class="text-sm text-slate-500">Pantau armada per mitra dan tugaskan sopir ke kendaraan.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">Kembali ke Ringkasan</a>
        </div>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Total Unit</p>
                    <span class="h-3 w-3 rounded-full bg-blue-900"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">{{ $stats['total'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Seluruh kendaraan terdaftar</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Tersedia</p>
                    <span class="h-3 w-3 rounded-full bg-[#3F7D6C]"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">{{ $stats['tersedia'] }}</p>
                <p class="mt-2 text-sm text-slate-500">Siap disewa</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Disewa / Maintenance</p>
                    <span class="h-3 w-3 rounded-full bg-[#E8A33D]"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">{{ $stats['disewa'] + $stats['maintenance'] }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ $stats['disewa'] }} disewa · {{ $stats['maintenance'] }} maintenance</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Pending Approval</p>
                    <span class="h-3 w-3 rounded-full bg-[#C1443C]"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">{{ $stats['pendingApproval'] }}</p>
                <p class="mt-2 text-sm text-slate-500">{{ $stats['pendingApproval'] > 0 ? 'Unit perlu ditinjau' : 'Semua disetujui' }}</p>
            </article>
        </section>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Armada Mitra</h2>
                    <p class="text-sm text-slate-500">Pilih mitra untuk melihat armadanya.</p>
                </div>
                <select x-model="selectedMitraId" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-700 transition focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]/20">
                    <option value="all">Semua Mitra</option>
                    @foreach ($mitras as $mitra)
                        <option value="{{ $mitra->id }}">{{ $mitra->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg bg-green-100 p-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        @foreach ($mitras as $mitra)
            @php $vList = $vehicles->where('mitra_id', $mitra->id); @endphp
            <div x-show="selectedMitraId === 'all' || selectedMitraId == '{{ $mitra->id }}'" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50/50 px-5 py-4">
                    <div>
                        <h3 class="font-[Barlow_Condensed] text-xl font-semibold text-slate-800">
                            <i class="fa-solid fa-handshake text-[#3F7D6C] mr-2"></i>{{ $mitra->name }}
                        </h3>
                        <p class="text-sm text-slate-500">{{ $mitra->email }} · {{ $vList->count() }} unit</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-medium">Unit</th>
                                <th class="px-5 py-3 font-medium">Plat</th>
                                <th class="px-5 py-3 font-medium">Jenis</th>
                                <th class="px-5 py-3 font-medium">Seater</th>
                                <th class="px-5 py-3 font-medium">Prioritas Mitra</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium">Harga/Hari</th>
                                <th class="px-5 py-3 font-medium">Sopir</th>
                                <th class="px-5 py-3 font-medium">Approval</th>
                                <th class="px-5 py-3 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($vList as $vehicle)
                                @php $status = $statusStyles[$vehicle->status] ?? ['label' => $vehicle->status, 'class' => 'bg-slate-100 text-slate-700']; @endphp
                                <tr class="odd:bg-white even:bg-slate-50/50">
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-3">
                                            @if ($vehicle->foto)
                                                <img src="{{ Storage::url($vehicle->foto) }}" alt="{{ $vehicle->nama }}" class="h-10 w-10 rounded-lg object-cover">
                                            @else
                                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-500">N/A</div>
                                            @endif
                                            <span class="font-medium text-slate-800">{{ $vehicle->nama }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">{{ $vehicle->plat_nomor }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ ucfirst($vehicle->jenis) }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $vehicle->kapasitas_penumpang }} orang</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $vehicle->prioritas_travel ?: 'Urutan daftar' }}</td>
                                    <td class="px-5 py-4">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $status['class'] }}">{{ $status['label'] }}</span>
                                    </td>
                                    <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($vehicle->harga_sewa_per_hari, 0, ',', '.') }}</td>
                                    <td class="px-5 py-4">
                                        @if ($vehicle->is_approved)
                                            <form action="{{ route('admin.vehicles.assign-driver', $vehicle) }}" method="POST" class="flex items-center gap-2">
                                                @csrf
                                                <select name="sopir_id" class="rounded-lg border border-slate-300 px-2 py-1 text-sm" onchange="this.form.submit()">
                                                    <option value="">— Pilih —</option>
                                                    @foreach ($drivers as $d)
                                                        <option value="{{ $d->id }}" @selected($vehicle->sopir_id == $d->id)>{{ $d->name }}</option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if ($vehicle->is_approved)
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Disetujui</span>
                                        @else
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Menunggu</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        @if (!$vehicle->is_approved)
                                            <form action="{{ route('admin.vehicles.approve', $vehicle) }}" method="POST" class="inline">
                                                @csrf
                                                <button class="rounded-lg bg-[#3F7D6C] px-3 py-2 text-sm font-medium text-white transition hover:opacity-90">Setujui</button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada unit armada dari mitra ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endforeach
    </div>
@endsection
