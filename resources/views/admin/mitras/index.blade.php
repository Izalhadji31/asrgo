@extends('layouts.admin')

@section('content')
    @php
        $statusStyles = [
            'tersedia' => ['label' => 'Tersedia', 'class' => 'bg-green-100 text-green-700'],
            'disewa' => ['label' => 'Disewa', 'class' => 'bg-blue-100 text-blue-700'],
            'maintenance' => ['label' => 'Maintenance', 'class' => 'bg-amber-100 text-amber-700'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Kelola Mitra</h1>
                <p class="text-sm text-slate-500">Kelola akun mitra, lihat armada, dan tugaskan sopir ke kendaraan.</p>
            </div>
            <button x-data @click="$dispatch('open-modal', 'create-mitra')" class="inline-flex items-center gap-2 rounded-lg bg-[#3F7D6C] px-4 py-2.5 text-sm font-medium text-white transition hover:opacity-90">
                <i class="fas fa-plus"></i> Buat Akun Mitra
            </button>
        </div>

        @if (session('success'))
            <div class="rounded-lg bg-green-100 p-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        @forelse ($mitras as $mitra)
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" x-data="{ open: false }">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <button @click="open = !open" class="flex h-10 w-10 items-center justify-center rounded-xl transition"
                            :class="open ? 'bg-blue-900 text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200'">
                            <i class="fa-solid fa-chevron-down text-sm transition-transform" :class="open ? 'rotate-180' : ''"></i>
                        </button>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-800">{{ $mitra->name }}</h3>
                            <p class="text-sm text-slate-500">{{ $mitra->email }} · {{ $mitra->vehicles->count() }} unit armada</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                    </div>
                </div>

                <div x-show="open" x-collapse>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50 text-left text-slate-500">
                                <tr>
                                    <th class="px-5 py-3 font-medium">Unit</th>
                                    <th class="px-5 py-3 font-medium">Plat</th>
                                    <th class="px-5 py-3 font-medium">Jenis</th>
                                    <th class="px-5 py-3 font-medium">Status</th>
                                    <th class="px-5 py-3 font-medium">Harga/Hari</th>
                                    <th class="px-5 py-3 font-medium">Sopir</th>
                                    <th class="px-5 py-3 font-medium">Approval</th>
                                    <th class="px-5 py-3 font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($mitra->vehicles as $vehicle)
                                    @php $status = $statusStyles[$vehicle->status] ?? ['label' => $vehicle->status, 'class' => 'bg-slate-100 text-slate-700']; @endphp
                                    <tr>
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
                                                            @php $alreadyAssigned = in_array($d->id, $assignedDriverIds) && $vehicle->sopir_id != $d->id; @endphp
                                                            @if (!$alreadyAssigned)
                                                                <option value="{{ $d->id }}" @selected($vehicle->sopir_id == $d->id)>{{ $d->name }}</option>
                                                            @endif
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
                                        <td colspan="8" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada unit armada dari mitra ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm">
                <i class="fa-solid fa-handshake text-4xl text-slate-300"></i>
                <p class="mt-3 text-sm font-medium text-slate-500">Belum ada data mitra.</p>
            </div>
        @endforelse
    </div>

    <x-modal name="create-mitra" maxWidth="lg">
        <div class="p-6">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Buat Akun Mitra</h2>
                <button x-on:click="$dispatch('close')" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form action="{{ route('admin.mitras.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]/20" required>
                        @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]/20" required>
                        @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                        <input type="password" name="password" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]/20" required>
                        @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]/20" required>
                    </div>
                </div>
                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="rounded-xl bg-[#3F7D6C] px-6 py-2.5 text-sm font-medium text-white transition hover:opacity-90">Buat Akun</button>
                    <button type="button" x-on:click="$dispatch('close')" class="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">Batal</button>
                </div>
            </form>
        </div>
    </x-modal>
@endsection
