@extends('layouts.mitra')

@section('content')
    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-slate-900">Tambah Unit Kendaraan</h1>
            <p class="mt-1 text-sm text-slate-500">Daftarkan kendaraan baru ke dalam armada Anda.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('vehicles.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Nama Kendaraan</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 transition focus:border-[#3F7D6C] focus:outline-none focus:ring-2 focus:ring-[#3F7D6C]/20" placeholder="Contoh: Toyota Avanza 2022" required>
                        @error('nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Plat Nomor</label>
                        <input type="text" name="plat_nomor" value="{{ old('plat_nomor') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 font-[IBM_Plex_Mono] text-sm text-slate-800 placeholder-slate-400 transition focus:border-[#3F7D6C] focus:outline-none focus:ring-2 focus:ring-[#3F7D6C]/20" placeholder="Contoh: B 1234 XYZ" required>
                        @error('plat_nomor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Jenis Kendaraan</label>
                        <input type="text" name="jenis" value="{{ old('jenis') }}" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 placeholder-slate-400 transition focus:border-[#3F7D6C] focus:outline-none focus:ring-2 focus:ring-[#3F7D6C]/20" placeholder="Contoh: MPV, SUV, Sedan" required>
                        @error('jenis') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Kapasitas Penumpang</label>
                        <div class="relative">
                            <input type="number" name="kapasitas_penumpang" value="{{ old('kapasitas_penumpang', 4) }}" min="1" max="100" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 pr-20 text-sm text-slate-800 transition focus:border-[#3F7D6C] focus:outline-none focus:ring-2 focus:ring-[#3F7D6C]/20" placeholder="Contoh: 7" required>
                            <span class="absolute inset-y-0 right-4 flex items-center text-sm text-slate-500">seater</span>
                        </div>
                        @error('kapasitas_penumpang') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Prioritas Antrean Travel</label>
                        <input type="number" name="prioritas_travel" value="{{ old('prioritas_travel') }}" min="1" max="1000" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-[#3F7D6C] focus:outline-none focus:ring-2 focus:ring-[#3F7D6C]/20" placeholder="Kosongkan untuk urutan pendaftaran">
                        <p class="mt-1 text-xs text-slate-500">Angka lebih kecil diprioritaskan lebih dulu. Hanya Anda sebagai mitra yang mengatur ini.</p>
                        @error('prioritas_travel') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Status</label>
                        <select name="status" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-800 transition focus:border-[#3F7D6C] focus:outline-none focus:ring-2 focus:ring-[#3F7D6C]/20" required>
                            <option value="tersedia">Tersedia</option>
                            <option value="disewa">Disewa</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                        @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Harga Sewa Tanpa Sopir (per hari)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm text-slate-500">Rp</span>
                            <input type="number" name="harga_sewa_tanpa_sopir_per_hari" value="{{ old('harga_sewa_tanpa_sopir_per_hari') }}" class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-[#3F7D6C] focus:outline-none focus:ring-2 focus:ring-[#3F7D6C]/20" placeholder="0" required>
                        </div>
                        @error('harga_sewa_tanpa_sopir_per_hari') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Harga Sewa Dengan Sopir (per hari)</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-sm text-slate-500">Rp</span>
                            <input type="number" name="harga_sewa_dengan_sopir_per_hari" value="{{ old('harga_sewa_dengan_sopir_per_hari') }}" class="w-full rounded-xl border border-slate-300 py-2.5 pl-10 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-[#3F7D6C] focus:outline-none focus:ring-2 focus:ring-[#3F7D6C]/20" placeholder="0" required>
                        </div>
                        @error('harga_sewa_dengan_sopir_per_hari') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-slate-700">Foto Kendaraan</label>
                        <div class="flex items-center gap-3">
                            <label class="flex cursor-pointer items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm text-slate-600 transition hover:border-[#3F7D6C] hover:bg-slate-50">
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd" />
                                </svg>
                                Pilih Foto
                                <input type="file" name="foto" accept="image/*" class="hidden">
                            </label>
                            <span class="text-xs text-slate-400">JPG, JPEG, PNG (max 2MB)</span>
                        </div>
                        @error('foto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-8 flex items-center gap-3 border-t border-slate-100 pt-6">
                    <button type="submit" class="rounded-xl bg-[#3F7D6C] px-6 py-2.5 text-sm font-medium text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-[#3F7D6C] focus:ring-offset-2">Simpan Unit</button>
                    <a href="{{ route('vehicles.index') }}" class="rounded-xl border border-slate-200 px-6 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-100">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
