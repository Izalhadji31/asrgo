@extends('layouts.mitra')

@section('content')
    @php
        $statusStyles = [
            'tersedia'    => ['label' => 'Tersedia', 'class' => 'bg-green-100 text-green-700'],
            'disewa'      => ['label' => 'Disewa', 'class' => 'bg-blue-100 text-blue-700'],
            'maintenance' => ['label' => 'Maintenance', 'class' => 'bg-amber-100 text-amber-700'],
        ];
        $total = $vehicles->count();
        $tersedia = $vehicles->where('status', 'tersedia')->count();
        $disewa = $vehicles->where('status', 'disewa')->count();
        $maintenance = $vehicles->where('status', 'maintenance')->count();
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-slate-900">Kelola Kendaraan</h1>
                <p class="text-sm text-slate-500">Daftar unit armada yang Anda kelola.</p>
            </div>
            <a href="{{ route('vehicles.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-[#3F7D6C] px-4 py-2.5 text-sm font-medium text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#3F7D6C] focus:ring-offset-2">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Tambah Unit
            </a>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Total Unit</p>
                    <span class="h-3 w-3 rounded-full bg-[#0F172A]"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $total }}</p>
                <p class="mt-2 text-sm text-slate-500">Seluruh kendaraan terdaftar</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Tersedia</p>
                    <span class="h-3 w-3 rounded-full bg-[#3F7D6C]"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $tersedia }}</p>
                <p class="mt-2 text-sm text-slate-500">Siap disewa</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Disewa</p>
                    <span class="h-3 w-3 rounded-full bg-[#2563EB]"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $disewa }}</p>
                <p class="mt-2 text-sm text-slate-500">Sedang dipakai</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Maintenance</p>
                    <span class="h-3 w-3 rounded-full bg-[#E8A33D]"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $maintenance }}</p>
                <p class="mt-2 text-sm text-slate-500">Dalam perbaikan</p>
            </article>
        </section>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-slate-900">Daftar Armada</h2>
                <p class="text-sm text-slate-500">Semua unit kendaraan milik Anda.</p>
            </div>

            @if (session('success'))
                <div class="mx-5 mt-4 rounded-lg bg-green-100 p-3 text-sm text-green-700">{{ session('success') }}</div>
            @endif

            @if ($vehicles->isNotEmpty())
                <form id="priority-form" action="{{ route('vehicles.reorder') }}" method="POST" class="mx-5 my-4 flex flex-col gap-3 rounded-xl border border-[#3F7D6C]/20 bg-[#3F7D6C]/5 p-4 sm:flex-row sm:items-center sm:justify-between">
                    @csrf
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Atur antrean kendaraan travel</p>
                        <p class="text-xs text-slate-500">Tarik baris ke atas/bawah, lalu simpan urutan. Urutan ini menjadi prioritas kendaraan Anda.</p>
                    </div>
                    <div id="priority-inputs">
                        @foreach ($vehicles as $vehicle)
                            <input type="hidden" name="vehicle_ids[]" value="{{ $vehicle->id }}">
                        @endforeach
                    </div>
                    <button type="submit" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-[#3F7D6C] px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                        <i class="fas fa-save"></i>
                        Simpan Urutan
                    </button>
                </form>
            @endif

            <div id="priority-table" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Urutan</th>
                            <th class="px-5 py-3 font-medium">Unit</th>
                            <th class="px-5 py-3 font-medium">Plat Nomor</th>
                            <th class="px-5 py-3 font-medium">Jenis</th>
                            <th class="px-5 py-3 font-medium">Seater</th>
                            <th class="px-5 py-3 font-medium">Prioritas</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Harga/Hari</th>
                            <th class="px-5 py-3 font-medium">Harga+Sopir</th>
                            <th class="px-5 py-3 font-medium">Approval</th>
                            <th class="px-5 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($vehicles as $vehicle)
                            @php $sts = $statusStyles[$vehicle->status] ?? ['label' => $vehicle->status, 'class' => 'bg-slate-100 text-slate-700']; @endphp
                            <tr draggable="true" data-priority-row data-vehicle-id="{{ $vehicle->id }}" class="cursor-move odd:bg-white even:bg-slate-50/50 transition hover:bg-[#3F7D6C]/5">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <i class="fas fa-grip-vertical text-slate-400" title="Tarik untuk mengubah urutan"></i>
                                        <span data-priority-number class="font-[IBM_Plex_Mono] text-sm font-semibold text-[#3F7D6C]">{{ $loop->iteration }}</span>
                                        <div class="flex flex-col gap-0.5 sm:hidden">
                                            <button type="button" data-move-up class="text-slate-400 hover:text-[#3F7D6C]" aria-label="Naikkan urutan"><i class="fas fa-chevron-up"></i></button>
                                            <button type="button" data-move-down class="text-slate-400 hover:text-[#3F7D6C]" aria-label="Turunkan urutan"><i class="fas fa-chevron-down"></i></button>
                                        </div>
                                    </div>
                                </td>
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
                                <td class="px-5 py-4 capitalize text-slate-600">{{ $vehicle->jenis }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $vehicle->kapasitas_penumpang }} orang</td>
                                <td class="px-5 py-4 text-slate-600">{{ $vehicle->prioritas_travel ?: 'Urutan daftar' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $sts['class'] }}">{{ $sts['label'] }}</span>
                                </td>
                                <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($vehicle->harga_sewa_tanpa_sopir_per_hari, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($vehicle->harga_sewa_dengan_sopir_per_hari, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    @if ($vehicle->is_approved)
                                        <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Disetujui</span>
                                    @else
                                        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Menunggu</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('vehicles.edit', $vehicle) }}" class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700 transition hover:bg-slate-100">Edit</a>
                                        <form action="{{ route('vehicles.destroy', $vehicle) }}" method="POST" onsubmit="return confirm('Hapus unit ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                    <td colspan="11" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-10 w-10 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                                        </svg>
                                        <p class="text-sm font-medium text-slate-500">Belum ada unit armada</p>
                                        <a href="{{ route('vehicles.create') }}" class="mt-1 rounded-lg bg-[#3F7D6C] px-4 py-2 text-sm font-medium text-white transition hover:opacity-90">Tambah Unit Pertama</a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if ($vehicles->isNotEmpty())
    <script>
        (() => {
            const table = document.getElementById('priority-table');
            const inputContainer = document.getElementById('priority-inputs');
            let draggedRow = null;

            const rows = () => [...table.querySelectorAll('[data-priority-row]')];

            const syncPriority = () => {
                inputContainer.replaceChildren();

                rows().forEach((row, index) => {
                    row.querySelector('[data-priority-number]').textContent = index + 1;

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'vehicle_ids[]';
                    input.value = row.dataset.vehicleId;
                    inputContainer.appendChild(input);
                });
            };

            rows().forEach((row) => {
                row.addEventListener('dragstart', () => {
                    draggedRow = row;
                    row.classList.add('opacity-40');
                });

                row.addEventListener('dragend', () => {
                    draggedRow = null;
                    row.classList.remove('opacity-40');
                    syncPriority();
                });

                row.addEventListener('dragover', (event) => event.preventDefault());
                row.addEventListener('drop', (event) => {
                    event.preventDefault();
                    if (!draggedRow || draggedRow === row) return;

                    const tableBody = row.parentNode;
                    const rowIndex = rows().indexOf(row);
                    const draggedIndex = rows().indexOf(draggedRow);

                    if (draggedIndex < rowIndex) {
                        tableBody.insertBefore(draggedRow, row.nextSibling);
                    } else {
                        tableBody.insertBefore(draggedRow, row);
                    }

                    syncPriority();
                });

                row.querySelector('[data-move-up]').addEventListener('click', () => {
                    const previous = row.previousElementSibling;
                    if (previous?.matches('[data-priority-row]')) {
                        row.parentNode.insertBefore(row, previous);
                        syncPriority();
                    }
                });

                row.querySelector('[data-move-down]').addEventListener('click', () => {
                    const next = row.nextElementSibling;
                    if (next?.matches('[data-priority-row]')) {
                        row.parentNode.insertBefore(next, row);
                        syncPriority();
                    }
                });
            });
        })();
    </script>
    @endif
@endsection
