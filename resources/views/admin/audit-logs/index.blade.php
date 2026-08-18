@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Riwayat Aktivitas</h1>
            <p class="text-sm text-slate-500">Catatan aksi penting admin pada sistem (audit log).</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Waktu</th>
                        <th class="px-5 py-3 font-medium">Admin</th>
                        <th class="px-5 py-3 font-medium">Aksi</th>
                        <th class="px-5 py-3 font-medium">Deskripsi</th>
                        <th class="px-5 py-3 font-medium">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        <tr class="odd:bg-white even:bg-slate-50/50">
                            <td class="px-5 py-4 text-slate-600">{{ $log->created_at?->translatedFormat('d M Y H:i') }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $log->user?->name ?? 'Sistem' }}</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 font-[IBM_Plex_Mono] text-xs text-slate-600">{{ $log->action }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-600">{{ $log->description }}</td>
                            <td class="px-5 py-4 font-[IBM_Plex_Mono] text-xs text-slate-400">{{ $log->ip_address ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-sm text-slate-500">Belum ada aktivitas tercatat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-5 py-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
