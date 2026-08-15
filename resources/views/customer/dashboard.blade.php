@extends('layouts.customer')

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
        $refundStatusLabels = [
            'requested' => ['label' => 'Refund Menunggu Review', 'class' => 'bg-orange-100 text-orange-700'],
            'rejected' => ['label' => 'Refund Ditolak', 'class' => 'bg-red-100 text-red-700'],
            'pending' => ['label' => 'Refund Diproses Midtrans', 'class' => 'bg-blue-100 text-blue-700'],
            'completed' => ['label' => 'Refund Selesai', 'class' => 'bg-emerald-100 text-emerald-700'],
            'failed' => ['label' => 'Refund Gagal', 'class' => 'bg-red-100 text-red-700'],
        ];
    @endphp

    <div class="space-y-6" x-data="paymentStatusWatcher(@js(route('payments.statuses')))">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-slate-900">Dashboard Pelanggan</h1>
                <p class="mt-1 text-sm text-slate-500">Pantau booking aktif dan riwayat pemesanan Anda.</p>
            </div>
            <a href="{{ route('bookings.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-blue-900 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-300 focus:ring-offset-2">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Buat Booking
            </a>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Total Booking</p>
                    <span class="h-3 w-3 rounded-full bg-teal-600"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $totalBookings }}</p>
                <p class="mt-2 text-sm text-slate-500">Seluruh pemesanan</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Booking Aktif</p>
                    <span class="h-3 w-3 rounded-full bg-blue-500"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $activeBookings->count() }}</p>
                <p class="mt-2 text-sm text-slate-500">Sedang diproses</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Selesai</p>
                    <span class="h-3 w-3 rounded-full bg-green-600"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $completedCount }}</p>
                <p class="mt-2 text-sm text-slate-500">Booking diselesaikan</p>
            </article>

            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Dibatalkan</p>
                    <span class="h-3 w-3 rounded-full bg-red-500"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $cancelledCount }}</p>
                <p class="mt-2 text-sm text-slate-500">Booking dibatalkan</p>
            </article>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-slate-900">Booking Aktif</h2>
                    <p class="text-sm text-slate-500">Booking yang sedang berlangsung atau menunggu konfirmasi.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-medium">Layanan</th>
                                <th class="px-5 py-3 font-medium">Kendaraan</th>
                                <th class="px-5 py-3 font-medium">Tanggal</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($activeBookings as $booking)
                                @php $statusKey = in_array($booking->status, ['completed', 'cancelled']) ? $booking->status : $booking->ticket_status;
                                   $s = $statusStyles[$statusKey] ?? ['label' => $statusKey, 'class' => 'bg-slate-100 text-slate-700'];
                                   $st = $serviceTypeLabels[$booking->service_type] ?? ['label' => $booking->service_type, 'class' => 'bg-slate-100 text-slate-700'];
                                   $sn = $sessionLabels[$booking->session] ?? null; @endphp
                                <tr class="odd:bg-white even:bg-slate-50/50">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-1.5">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $st['class'] }}">{{ $st['label'] }}</span>
                                            @if ($sn)
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $sn['class'] }}">{{ $sn['label'] }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 font-medium text-slate-700">{{ $booking->vehicle?->nama ?? '-' }}</td>
                                    <td class="px-5 py-3 text-slate-600">
                                        {{ \Carbon\Carbon::parse($booking->tanggal_mulai)->format('d M') }} — {{ \Carbon\Carbon::parse($booking->tanggal_selesai)->format('d M Y') }}
                                    </td>
                                     <td class="px-5 py-3">
                                         @if ($booking->payment_status !== 'paid')
                                             <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">{{ ucfirst($booking->payment_status ?? 'unpaid') }}</span>
                                          @else
                                              <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $s['class'] }}">{{ $s['label'] }}</span>
                                          @endif
                                          @if ($booking->refund_status !== 'none' && isset($refundStatusLabels[$booking->refund_status]))
                                              <span class="mt-1 inline-flex rounded-full px-2 py-1 text-[11px] font-semibold {{ $refundStatusLabels[$booking->refund_status]['class'] }}">
                                                  {{ $refundStatusLabels[$booking->refund_status]['label'] }}
                                              </span>
                                          @endif
                                      </td>
                                     <td class="px-5 py-3">
                                         @if (in_array($booking->payment_status, ['unpaid', 'failed', 'expired'], true))
                                             <div class="flex items-center gap-2">
                                                 <a href="{{ route('payments.show', $booking) }}" class="rounded-lg bg-blue-900 px-3 py-1.5 text-xs font-medium text-white">Bayar</a>
                                                 <form action="{{ route('bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Batalkan booking ini?')">
                                                     @csrf
                                                     <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600">Batal</button>
                                                 </form>
                                             </div>
                                         @elseif ($booking->payment_status === 'pending')
                                             <span class="text-xs text-amber-600">Menunggu pembayaran</span>
                                         @elseif ($booking->ticket_number)
                                             <a href="{{ route('ticket.show', $booking) }}" class="rounded-lg border border-[#3F7D6C] px-3 py-1.5 text-xs font-medium text-[#3F7D6C]">Tiket</a>
                                         @else
                                             <span class="text-xs text-slate-400">Menunggu tiket</span>
                                         @endif
                                     </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">
                                        Belum ada booking aktif.
                                        <a href="{{ route('bookings.create') }}" class="ml-1 font-medium text-blue-900 hover:text-blue-800">Buat sekarang</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 px-5 py-4">
                    <h2 class="font-[Barlow_Condensed] text-2xl font-semibold text-slate-900">Riwayat Terakhir</h2>
                    <p class="text-sm text-slate-500">5 booking terakhir yang sudah selesai atau dibatalkan.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-left text-slate-500">
                            <tr>
                                <th class="px-5 py-3 font-medium">Kendaraan</th>
                                <th class="px-5 py-3 font-medium">Status</th>
                                <th class="px-5 py-3 font-medium">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($history as $item)
                                @php $statusKey = in_array($item->status, ['completed', 'cancelled']) ? $item->status : $item->ticket_status;
                                   $s = $statusStyles[$statusKey] ?? ['label' => $statusKey, 'class' => 'bg-slate-100 text-slate-700']; @endphp
                                <tr class="odd:bg-white even:bg-slate-50/50">
                                    <td class="px-5 py-3 font-medium text-slate-700">{{ $item->vehicle?->nama ?? '-' }}</td>
                                    <td class="px-5 py-3">
                                        <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $s['class'] }}">{{ $s['label'] }}</span>
                                    </td>
                                    <td class="px-5 py-3 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($item->total_harga, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-500">Belum ada riwayat booking.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
