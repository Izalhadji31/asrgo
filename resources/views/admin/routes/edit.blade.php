@extends('layouts.admin')

@php
    $assignmentPagi = $route->assignments->firstWhere('session', 'pagi');
    $assignmentSiang = $route->assignments->firstWhere('session', 'siang');
@endphp

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Edit Rute</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $route->origin }} → {{ $route->destination }}</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('admin.routes.update', $route) }}" method="POST">
                <input type="hidden" name="service_type" value="travel">
                @csrf
                @method('PUT')
                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Asal / Kota Asal</label>
                        <input type="text" name="origin" value="{{ old('origin', $route->origin) }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]/20" required>
                        @error('origin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Tujuan / Kota Tujuan</label>
                        <input type="text" name="destination" value="{{ old('destination', $route->destination) }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]/20" required>
                        @error('destination') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Harga</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm text-slate-500">Rp</span>
                            <input type="number" name="price" value="{{ old('price', $route->price) }}" class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm text-slate-800 transition focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]/20" required>
                        </div>
                        @error('price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div></div>

                    {{-- Sesi Pagi --}}
                    <div class="md:col-span-2 rounded-xl border border-sky-200 bg-sky-50/50 p-5">
                        <h3 class="mb-3 font-semibold text-sky-800"><i class="fas fa-sun mr-2"></i>Sesi Pagi (08:00 — 12:00)</h3>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Mitra Keberangkatan Pagi</label>
                            <p class="mb-2 text-xs text-slate-500">Pilih mitra yang berada di kota asal. Semua kendaraan mitra akan menjadi antrean sesi ini.</p>
                            <select name="mitra_id_pagi" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]/20">
                                <option value="">— Pilih mitra (opsional) —</option>
                                @foreach ($mitras as $mitra)
                                    <option value="{{ $mitra->id }}" @selected(old('mitra_id_pagi', $assignmentPagi?->mitra_id) == $mitra->id)>{{ $mitra->name }} — {{ $mitra->vehicles_count }} kendaraan</option>
                                @endforeach
                            </select>
                            @error('mitra_id_pagi') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Sesi Siang --}}
                    <div class="md:col-span-2 rounded-xl border border-orange-200 bg-orange-50/50 p-5">
                        <h3 class="mb-3 font-semibold text-orange-800"><i class="fas fa-cloud-sun mr-2"></i>Sesi Siang (12:00 — 17:00)</h3>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-slate-700">Mitra Keberangkatan Siang</label>
                            <p class="mb-2 text-xs text-slate-500">Pilih mitra yang berada di kota asal. Semua kendaraan mitra akan menjadi antrean sesi ini.</p>
                            <select name="mitra_id_siang" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]/20">
                                <option value="">— Pilih mitra (opsional) —</option>
                                @foreach ($mitras as $mitra)
                                    <option value="{{ $mitra->id }}" @selected(old('mitra_id_siang', $assignmentSiang?->mitra_id) == $mitra->id)>{{ $mitra->name }} — {{ $mitra->vehicles_count }} kendaraan</option>
                                @endforeach
                            </select>
                            @error('mitra_id_siang') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex items-center gap-3 border-t border-slate-100 pt-6">
                    <button type="submit" class="rounded-xl bg-[#3F7D6C] px-6 py-2.5 text-sm font-medium text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#3F7D6C] focus:ring-offset-2">Simpan Perubahan</button>
                    <a href="{{ route('admin.routes.index') }}" class="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
