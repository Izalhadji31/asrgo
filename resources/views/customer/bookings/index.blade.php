@extends('layouts.customer')

@section('content')
    @php
        $statusStyles = [
            'not_created'     => ['label' => 'Belum Dapat Tiket', 'class' => 'bg-amber-100 text-amber-700'],
            'created'         => ['label' => 'Tiket Dibuat', 'class' => 'bg-blue-100 text-blue-700'],
            'completed'       => ['label' => 'Selesai', 'class' => 'bg-green-100 text-green-700'],
            'cancelled'       => ['label' => 'Dibatalkan', 'class' => 'bg-red-100 text-red-700'],
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
        $total = $bookings->count();
        $active = $bookings->whereIn('status', ['pending', 'sopir_assigned'])->count();
        $completed = $bookings->where('status', 'completed')->count();
        $cancelled = $bookings->where('status', 'cancelled')->count();
    @endphp

    <div class="space-y-6" x-data="paymentStatusWatcher(@js(route('payments.statuses')))">
        <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-[Barlow_Condensed] text-3xl font-semibold text-slate-900">Riwayat Booking</h1>
                <p class="mt-1 text-sm text-slate-500">Semua pemesanan kendaraan Anda.</p>
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
                    <p class="text-sm font-medium text-slate-500">Total</p>
                    <span class="h-3 w-3 rounded-full bg-teal-600"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $total }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Aktif</p>
                    <span class="h-3 w-3 rounded-full bg-blue-500"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $active }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Selesai</p>
                    <span class="h-3 w-3 rounded-full bg-green-600"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $completed }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <p class="text-sm font-medium text-slate-500">Dibatalkan</p>
                    <span class="h-3 w-3 rounded-full bg-red-500"></span>
                </div>
                <p class="font-[IBM_Plex_Mono] text-2xl font-semibold text-slate-900">{{ $cancelled }}</p>
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
                            <th class="px-5 py-3 font-medium">Sopir</th>
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
                                <td class="px-5 py-4 text-slate-600">{{ $booking->sopir?->name ?? '-' }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ \Carbon\Carbon::parse($booking->tanggal_mulai)->format('d M Y') }}</td>
                                <td class="px-5 py-4 text-slate-600">{{ \Carbon\Carbon::parse($booking->tanggal_selesai)->format('d M Y') }}</td>
                                 <td class="px-5 py-4">
                                     @if ($booking->payment_status !== 'paid')
                                         <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">
                                             {{ match ($booking->payment_status) {
                                                 'pending' => 'Menunggu Pembayaran',
                                                 'failed' => 'Pembayaran Gagal',
                                                 'expired' => 'Pembayaran Kedaluwarsa',
                                                 default => 'Belum Dibayar',
                                             } }}
                                         </span>
                                      @else
                                          <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $s['class'] }}">{{ $s['label'] }}</span>
                                      @endif
                                      @if ($booking->refund_status !== 'none' && isset($refundStatusLabels[$booking->refund_status]))
                                          <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $refundStatusLabels[$booking->refund_status]['class'] }}">
                                              {{ $refundStatusLabels[$booking->refund_status]['label'] }}
                                          </span>
                                          @if ($booking->refund_status === 'rejected' && $booking->refund_rejection_reason)
                                              <p class="mt-1 max-w-xs text-xs text-red-600">{{ $booking->refund_rejection_reason }}</p>
                                          @endif
                                      @endif
                                  </td>
                                <td class="px-5 py-4 font-[IBM_Plex_Mono] text-slate-700">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td>
                                 <td class="px-5 py-4">
                                     @if (!in_array($booking->status, ['cancelled', 'completed']))
                                         @if (in_array($booking->payment_status, ['unpaid', 'failed', 'expired'], true))
                                             <div class="flex items-center gap-2">
                                                 <a href="{{ route('payments.show', $booking) }}" class="rounded-lg bg-blue-900 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-blue-800">Bayar</a>
                                                 <form action="{{ route('bookings.cancel', $booking) }}" method="POST" onsubmit="return confirm('Batalkan booking ini?')">
                                                     @csrf
                                                     <button class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 transition hover:bg-red-50">Batal</button>
                                                 </form>
                                             </div>
                                          @elseif ($booking->payment_status === 'pending')
                                              <a href="{{ route('payments.show', $booking) }}" class="rounded-lg bg-blue-900 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-blue-800">
                                                  {{ $booking->payment_token && $booking->payment_expired_at?->isFuture() ? 'Lanjutkan Pembayaran' : 'Buka Pembayaran' }}
                                              </a>
                                          @elseif ($booking->refund_status === 'none')
                                              @if ($booking->ticket_number)
                                                  <a href="{{ route('ticket.show', $booking) }}" class="mb-2 inline-flex rounded-lg border border-[#3F7D6C] px-3 py-1.5 text-xs font-medium text-[#3F7D6C] transition hover:bg-[#3F7D6C] hover:text-white">Lihat Tiket</a>
                                              @endif
                                              <details class="max-w-xs text-left">
                                                  <summary class="cursor-pointer text-xs font-medium text-red-600">Ajukan Refund</summary>
                                                  <form action="{{ route('bookings.refund.request', $booking) }}" method="POST" class="mt-2 space-y-2 rounded-lg border border-red-100 bg-red-50 p-3">
                                                      @csrf
                                                      <label class="block text-xs font-medium text-red-800" for="refund-reason-{{ $booking->id }}">Alasan refund</label>
                                                      <textarea id="refund-reason-{{ $booking->id }}" name="refund_reason" rows="3" minlength="10" maxlength="1000" required class="w-full rounded-lg border border-red-200 px-2 py-1.5 text-xs text-slate-700" placeholder="Jelaskan alasan refund (minimal 10 karakter)"></textarea>
                                                      <button type="submit" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">Kirim Pengajuan</button>
                                                  </form>
                                              </details>
                                          @elseif ($booking->refund_status === 'requested')
                                              @if ($booking->ticket_number)
                                                  <a href="{{ route('ticket.show', $booking) }}" class="mb-2 inline-flex rounded-lg border border-[#3F7D6C] px-3 py-1.5 text-xs font-medium text-[#3F7D6C] transition hover:bg-[#3F7D6C] hover:text-white">Lihat Tiket</a>
                                              @endif
                                              <span class="text-xs text-orange-600">Menunggu review admin</span>
                                          @elseif ($booking->refund_status === 'pending')
                                              @if ($booking->ticket_number)
                                                  <a href="{{ route('ticket.show', $booking) }}" class="mb-2 inline-flex rounded-lg border border-[#3F7D6C] px-3 py-1.5 text-xs font-medium text-[#3F7D6C] transition hover:bg-[#3F7D6C] hover:text-white">Lihat Tiket</a>
                                              @endif
                                              <span class="text-xs text-blue-600">Menunggu proses Midtrans</span>
                                          @elseif ($booking->ticket_number)
                                             <a href="{{ route('ticket.show', $booking) }}" class="rounded-lg border border-[#3F7D6C] px-3 py-1.5 text-xs font-medium text-[#3F7D6C] transition hover:bg-[#3F7D6C] hover:text-white">Lihat Tiket</a>
                                         @else
                                             <span class="text-xs text-slate-400">Menunggu tiket</span>
                                         @endif
                                     @else
                                         @if ($booking->ticket_number)
                                             <a href="{{ route('ticket.show', $booking) }}" class="rounded-lg border border-[#3F7D6C] px-3 py-1.5 text-xs font-medium text-[#3F7D6C] transition hover:bg-[#3F7D6C] hover:text-white">Lihat Tiket</a>
                                         @else
                                             <span class="text-xs text-slate-400">—</span>
                                         @endif
                                     @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <svg class="h-10 w-10 text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15a2.25 2.25 0 012.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                        </svg>
                                        <p class="text-sm font-medium text-slate-500">Belum ada riwayat booking</p>
                                        <a href="{{ route('bookings.create') }}" class="mt-1 rounded-lg bg-blue-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-800">Buat Booking Pertama</a>
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
