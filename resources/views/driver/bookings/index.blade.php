@extends('layouts.driver')

@section('content')
    @php
        $statusStyles = [
            'not_created'    => ['label' => 'Belum Dapat Tiket', 'class' => 'bg-amber-100 text-amber-700'],
            'created'        => ['label' => 'Tiket Dibuat', 'class' => 'bg-blue-100 text-blue-700'],
            'completed'      => ['label' => 'Selesai', 'class' => 'bg-green-100 text-green-700'],
            'cancelled'      => ['label' => 'Dibatalkan', 'class' => 'bg-red-100 text-red-700'],
        ];
        $serviceTypeLabels = [
            'rental'  => ['label' => 'Rental', 'class' => 'bg-blue-100 text-blue-700'],
            'travel'  => ['label' => 'Travel', 'class' => 'bg-purple-100 text-purple-700'],
        ];
        $sessionLabels = [
            'pagi'  => ['label' => 'Pagi', 'class' => 'bg-sky-100 text-sky-700'],
            'siang' => ['label' => 'Siang', 'class' => 'bg-orange-100 text-orange-700'],
        ];
        $total = $bookings->count();
        $completed = $bookings->where('status', 'completed')->count();
        $ongoing = $bookings->whereIn('status', ['sopir_assigned', 'pending'])->count();
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-slate-900">Tugas Booking</h1>
            <p class="mt-1 text-sm text-slate-500">Semua booking yang ditugaskan kepada Anda.</p>
        </div>

        <section class="grid gap-4 sm:grid-cols-3">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Total Tugas</p>
                    <span class="h-3 w-3 rounded-full bg-indigo-600"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $total }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Sedang Berjalan</p>
                    <span class="h-3 w-3 rounded-full bg-indigo-500"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $ongoing }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Selesai</p>
                    <span class="h-3 w-3 rounded-full bg-green-600"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $completed }}</p>
            </article>
        </section>

        @if (session('success'))
            <div class="rounded-xl bg-green-100 p-4 text-sm text-green-700">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="rounded-xl bg-blue-100 p-4 text-sm text-blue-700">{{ session('info') }}</div>
        @endif

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50 text-left text-slate-500">
                        <tr>
                            <th class="px-5 py-3 font-medium">Layanan</th>
                            <th class="px-5 py-3 font-medium">Kendaraan</th>
                            <th class="px-5 py-3 font-medium">Plat</th>
                            <th class="px-5 py-3 font-medium">Pelanggan</th>
                            <th class="px-5 py-3 font-medium">Mulai</th>
                            <th class="px-5 py-3 font-medium">Selesai</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Total</th>
                            <th class="px-5 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($bookings as $booking)
                            @php $statusKey = in_array($booking->status, ['completed', 'cancelled']) ? $booking->status : $booking->ticket_status;
                                   $s = $statusStyles[$statusKey] ?? ['label' => $statusKey, 'class' => 'bg-slate-100 text-slate-700'];
                                   $st = $serviceTypeLabels[$booking->service_type] ?? ['label' => $booking->service_type, 'class' => 'bg-slate-100 text-slate-700'];
                                   $sn = $sessionLabels[$booking->session] ?? null; @endphp
                            <tr class="odd:bg-white even:bg-slate-50/50">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $st['class'] }}">{{ $st['label'] }}</span>
                                        @if ($sn)
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $sn['class'] }}">{{ $sn['label'] }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 font-medium text-slate-700">{{ $booking->vehicle?->nama ?? '-' }}</td>
                                <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-600">{{ $booking->vehicle?->plat_nomor ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <div class="font-medium text-slate-700">{{ $booking->pelanggan?->name ?? '-' }}</div>
                                    <div class="font-[IBM_Plex_Mono] text-xs text-slate-500">{{ $booking->contact_hp ?? $booking->passengers->first()?->no_hp ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-slate-600">{{ \Carbon\Carbon::parse($booking->tanggal_mulai)->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ \Carbon\Carbon::parse($booking->tanggal_selesai)->format('d M Y') }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $s['class'] }}">{{ $s['label'] }}</span>
                                    @if ($booking->sopir_id === Auth::id() && ! $booking->driver_confirmed_at && ! in_array($booking->status, ['completed', 'cancelled'], true))
                                        <span class="mt-1 block rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Menunggu Konfirmasi</span>
                                    @elseif ($booking->driver_confirmed_at)
                                        <span class="mt-1 block rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">Dikonfirmasi</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        @if ($booking->status !== 'completed' && $booking->status !== 'cancelled')
                                            @if (! $booking->driver_confirmed_at)
                                            <form action="{{ route('driver.bookings.accept', $booking) }}" method="POST">
                                                @csrf
                                                <button class="rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-emerald-700">Terima</button>
                                            </form>
                                            <form action="{{ route('driver.bookings.reject', $booking) }}" method="POST" onsubmit="return confirm('Tolak penugasan ini? Booking akan dikembalikan ke admin.')">
                                                @csrf
                                                <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50">Tolak</button>
                                            </form>
                                            @else
                                            <form action="{{ route('bookings.complete', $booking) }}" method="POST">
                                                @csrf
                                                <button class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-green-700">Selesai</button>
                                            </form>
                                            @endif
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-10 w-10 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                        </svg>
                                        <p class="text-sm font-medium text-slate-500">Belum ada tugas booking</p>
                                        <p class="text-xs text-slate-400">Tunggu admin menugaskan booking ke Anda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-100 px-5 py-4">
                {{ $bookings->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
