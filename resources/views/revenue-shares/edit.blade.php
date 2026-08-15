@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Edit Bagi Hasil</h1>
                <p class="text-sm text-slate-500">Perbarui aturan pembagian pendapatan yang sudah tersimpan.</p>
            </div>
            <a href="{{ route('admin.revenue-shares.index') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">Kembali</a>
        </div>

        @if ($errors->any())
            <div class="rounded-lg bg-red-100 p-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="max-w-3xl rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form action="{{ route('admin.revenue-shares.update', $revenueShare) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="mitra_id" class="mb-1 block text-sm font-medium text-slate-700">Mitra</label>
                    <select id="mitra_id" name="mitra_id" class="w-full rounded-lg border border-slate-300 bg-[#F5F4F0] px-3 py-2 text-sm text-slate-800 focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">
                        <option value="">Default Global</option>
                        @foreach ($mitras as $mitra)
                            <option value="{{ $mitra->id }}" @selected(old('mitra_id', $revenueShare->mitra_id) == $mitra->id)>{{ $mitra->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="persen_platform" class="mb-1 block text-sm font-medium text-slate-700">Persen Platform</label>
                        <input id="persen_platform" name="persen_platform" type="number" min="0" max="100" step="0.01" value="{{ old('persen_platform', $revenueShare->persen_platform) }}" class="w-full rounded-lg border border-slate-300 bg-[#F5F4F0] px-3 py-2 text-sm text-slate-800 focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]" required>
                    </div>
                    <div>
                        <label for="persen_mitra" class="mb-1 block text-sm font-medium text-slate-700">Persen Mitra</label>
                        <input id="persen_mitra" name="persen_mitra" type="number" min="0" max="100" step="0.01" value="{{ old('persen_mitra', $revenueShare->persen_mitra) }}" class="w-full rounded-lg border border-slate-300 bg-[#F5F4F0] px-3 py-2 text-sm text-slate-800 focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]" required>
                    </div>
                </div>
                <p class="text-xs text-slate-500">Total platform + mitra harus 100%.</p>
                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-950 focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">Simpan Perubahan</button>
                    <a href="{{ route('admin.revenue-shares.index') }}" class="rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-100">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
