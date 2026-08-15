@extends('layouts.admin')

@section('content')
    @php
        $statCards = [
            [
                'label' => 'Total Sopir',
                'value' => $stats['total'],
                'note' => 'Seluruh akun driver yang terdaftar',
                'accent' => 'bg-blue-900',
            ],
            [
                'label' => 'Aktif',
                'value' => $stats['approved'],
                'note' => 'Driver siap ditugaskan',
                'accent' => 'bg-[#3F7D6C]',
            ],
        ];

        $statGrid = 'md:grid-cols-2';
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Kelola Sopir</h1>
                <p class="text-sm text-slate-500">Lihat status akun sopir dan proses persetujuan akun driver.</p>
            </div>
            <div class="flex items-center gap-2">
                <button x-data @click="$dispatch('open-modal', 'create-driver')" class="inline-flex items-center gap-2 rounded-lg bg-[#3F7D6C] px-4 py-2.5 text-sm font-medium text-white transition hover:opacity-90">
                    <i class="fas fa-plus"></i> Buat Akun Sopir
                </button>
                <a href="{{ route('admin.dashboard') }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-[#E8A33D]">Kembali ke Ringkasan</a>
            </div>
        </div>

        <section class="grid gap-4 {{ $statGrid }}">
            @foreach ($statCards as $stat)
                <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="mb-4 flex items-center justify-between">
                        <p class="text-sm font-medium text-slate-500">{{ $stat['label'] }}</p>
                        <span class="h-3 w-3 rounded-full {{ $stat['accent'] }}"></span>
                    </div>
                    <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-blue-900">{{ $stat['value'] }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ $stat['note'] }}</p>
                </article>
            @endforeach
        </section>

        @if (session('success'))
            <div class="rounded-lg bg-green-100 p-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Daftar Sopir</h2>
                <p class="text-sm text-slate-500">Semua akun dengan role driver yang ada di sistem.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Nama</th>
                            <th class="px-5 py-3 font-medium">Email</th>
                            <th class="px-5 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($drivers as $driver)
                            <tr class="odd:bg-white even:bg-slate-50/50">
                                <td class="px-5 py-4 font-medium text-slate-700">{{ $driver->name }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ $driver->email }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Aktif</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada data sopir.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <x-modal name="create-driver" maxWidth="lg">
        <div class="p-6">
            <div class="mb-5 flex items-center justify-between">
                <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-blue-900">Buat Akun Sopir</h2>
                <button x-on:click="$dispatch('close')" class="rounded-lg p-1 text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <form action="{{ route('admin.drivers.store') }}" method="POST">
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
