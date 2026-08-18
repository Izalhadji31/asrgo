@extends('layouts.admin')

@section('content')
@php
    $roleLabels = [
        'admin' => ['label' => 'Admin', 'class' => 'bg-amber-100 text-amber-700'],
        'customer' => ['label' => 'Customer', 'class' => 'bg-green-100 text-green-700'],
        'mitra' => ['label' => 'Mitra', 'class' => 'bg-purple-100 text-purple-700'],
        'driver' => ['label' => 'Sopir', 'class' => 'bg-sky-100 text-sky-700'],
    ];
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Kelola Pengguna</h1>
            <p class="text-sm text-slate-500">Daftar seluruh pengguna sistem dan reset password.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-xl bg-green-100 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-end">
        <div class="flex-[2]">
            <label for="q" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Cari Pengguna</label>
            <input id="q" name="q" type="text" value="{{ $search }}" placeholder="Nama atau email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700">
        </div>
        <div class="flex-1">
            <label for="role" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-400">Role</label>
            <select id="role" name="role" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-700">
                <option value="">Semua Role</option>
                <option value="admin" @selected($role === 'admin')>Admin</option>
                <option value="customer" @selected($role === 'customer')>Customer</option>
                <option value="mitra" @selected($role === 'mitra')>Mitra</option>
                <option value="driver" @selected($role === 'driver')>Sopir</option>
            </select>
        </div>
        <button class="rounded-lg bg-blue-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-800">Cari</button>
        @if ($search !== '' || $role)
            <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-100">Reset</a>
        @endif
    </form>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Nama</th>
                        <th class="px-5 py-3 font-medium">Email</th>
                        <th class="px-5 py-3 font-medium">Role</th>
                        <th class="px-5 py-3 font-medium">Terdaftar</th>
                        <th class="px-5 py-3 font-medium">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($users as $user)
                        @php $rl = $roleLabels[$user->role] ?? ['label' => $user->role, 'class' => 'bg-slate-100 text-slate-700']; @endphp
                        <tr class="odd:bg-white even:bg-slate-50/50">
                            <td class="px-5 py-4 font-medium text-slate-800">{{ $user->name }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $user->email }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $rl['class'] }}">{{ $rl['label'] }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $user->created_at?->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-4">
                                <details class="text-left">
                                    <summary class="cursor-pointer text-xs font-medium text-[#E8A33D]">Reset Password</summary>
                                    <form action="{{ route('admin.users.reset-password', $user) }}" method="POST" class="mt-2 w-72 space-y-2 rounded-lg border border-amber-200 bg-amber-50 p-3">
                                        @csrf
                                        <input type="password" name="password" minlength="8" required placeholder="Password baru (min 8 karakter)" class="w-full rounded-lg border border-amber-200 px-2 py-1.5 text-xs text-slate-700">
                                        <input type="password" name="password_confirmation" minlength="8" required placeholder="Ulangi password" class="w-full rounded-lg border border-amber-200 px-2 py-1.5 text-xs text-slate-700">
                                        <button type="submit" class="rounded-lg bg-[#E8A33D] px-3 py-1.5 text-xs font-semibold text-blue-900 transition hover:opacity-90">Simpan Password Baru</button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">Tidak ada pengguna ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
