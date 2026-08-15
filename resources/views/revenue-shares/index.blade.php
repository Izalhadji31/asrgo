@extends('layouts.admin')

@section('content')
    @php
        $summaryCards = [
            [
                'label' => 'Total Aturan',
                'value' => $stats['total'],
                'note' => 'Seluruh konfigurasi bagi hasil',
                'accent' => 'bg-blue-900',
            ],
            [
                'label' => 'Global Default',
                'value' => $stats['global'],
                'note' => 'Berlaku jika mitra belum punya aturan khusus',
                'accent' => 'bg-[#3F7D6C]',
            ],
            [
                'label' => 'Khusus Mitra',
                'value' => $stats['specific'],
                'note' => 'Aturan yang ditautkan ke mitra tertentu',
                'accent' => 'bg-[#E8A33D]',
            ],
            [
                'label' => 'Split Aktif',
                'value' => $stats['active_platform'] . '% / ' . $stats['active_mitra'] . '%',
                'note' => 'Konfigurasi default terbaru',
                'accent' => 'bg-[#C1443C]',
            ],
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Bagi Hasil</h1>
                <p class="text-sm text-slate-500">Atur pembagian pendapatan antara platform dan mitra.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">Kembali ke Ringkasan</a>
        </div>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($summaryCards as $card)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <span class="h-3 w-3 rounded-full {{ $card['accent'] }}"></span>
                    </div>
                    <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">{{ $card['value'] }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $card['note'] }}</p>
                </article>
            @endforeach
        </section>

        @if (session('success'))
            <div class="rounded-lg bg-green-100 p-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-red-100 p-3 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4">
                    <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Tambah Aturan</h2>
                    <p class="text-sm text-slate-500">Buat aturan global atau khusus untuk satu mitra.</p>
                </div>

                <form action="{{ route('admin.revenue-shares.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="mitra_id" class="mb-1 block text-sm font-medium text-slate-700">Mitra</label>
                        <select id="mitra_id" name="mitra_id" class="w-full rounded-lg border border-slate-300 bg-[#F5F4F0] px-3 py-2 text-sm text-slate-800 focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">
                            <option value="">Default Global</option>
                            @foreach ($mitras as $mitra)
                                <option value="{{ $mitra->id }}" @selected(old('mitra_id') == $mitra->id)>{{ $mitra->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <div>
                            <label for="persen_platform" class="mb-1 block text-sm font-medium text-slate-700">Persen Platform</label>
                            <input id="persen_platform" name="persen_platform" type="number" min="0" max="100" step="0.01" value="{{ old('persen_platform', $globalShare?->persen_platform) }}" class="w-full rounded-lg border border-slate-300 bg-[#F5F4F0] px-3 py-2 text-sm text-slate-800 focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]" />
                        </div>
                        <div>
                            <label for="persen_mitra" class="mb-1 block text-sm font-medium text-slate-700">Persen Mitra</label>
                            <input id="persen_mitra" name="persen_mitra" type="number" min="0" max="100" step="0.01" value="{{ old('persen_mitra', $globalShare?->persen_mitra) }}" class="w-full rounded-lg border border-slate-300 bg-[#F5F4F0] px-3 py-2 text-sm text-slate-800 focus:border-[#E8A33D] focus:outline-none focus:ring-2 focus:ring-[#E8A33D]" />
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">Pastikan total platform + mitra = 100%.</p>
                    <button type="submit" class="w-full rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-950 focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">Simpan Aturan</button>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Daftar Aturan</h2>
                    <p class="text-sm text-slate-500">Semua pengaturan bagi hasil yang tersimpan di sistem.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-medium">Mitra</th>
                                <th class="px-5 py-3 font-medium">Platform</th>
                                <th class="px-5 py-3 font-medium">Mitra</th>
                                <th class="px-5 py-3 font-medium">Tipe</th>
                                <th class="px-5 py-3 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($revenueShares as $share)
                                <tr class="odd:bg-white even:bg-slate-50/50">
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-slate-700">{{ $share->mitra?->name ?? 'Default Global' }}</p>
                                        <p class="text-xs text-slate-500">{{ $share->mitra?->email ?? 'Berlaku untuk semua mitra' }}</p>
                                    </td>
                                    <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">{{ $share->persen_platform }}%</td>
                                    <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">{{ $share->persen_mitra }}%</td>
                                    <td class="px-5 py-4">
                                        @if ($share->mitra_id)
                                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Khusus Mitra</span>
                                        @else
                                            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Default</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <div class="flex flex-wrap gap-2">
                                            <a href="{{ route('admin.revenue-shares.edit', $share) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100">Edit</a>
                                            <form action="{{ route('admin.revenue-shares.destroy', $share) }}" method="POST" class="inline" onsubmit="return confirm('Hapus data ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="rounded-lg bg-[#C1443C] px-3 py-2 text-sm font-medium text-white transition hover:opacity-90">Hapus</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada pengaturan bagi hasil.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
@endsection
