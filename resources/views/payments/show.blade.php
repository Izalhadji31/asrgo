@extends('layouts.customer')

@section('content')
    <div class="mx-auto max-w-2xl space-y-6" x-data="{
        message: '',
        polling: null,
        init() {
            this.polling = window.setInterval(() => this.checkStatus(), 3000);
        },
        async checkStatus() {
            const response = await fetch(@js(route('payments.status', $booking)), {
                headers: { 'Accept': 'application/json' },
            });
            const data = await response.json();

            if (data.payment_status === 'paid') {
                window.clearInterval(this.polling);
                window.location.href = @js(route('bookings.index'));
            }
        },
        pay() {
            if (!window.snap) {
                this.message = 'Layanan pembayaran belum termuat. Muat ulang halaman.';
                return;
            }

            window.snap.pay(@js($snapToken), {
                onSuccess: () => {
                    this.message = 'Pembayaran berhasil. Menunggu konfirmasi dari Midtrans...';
                    this.checkStatus();
                },
                onPending: () => {
                    this.message = 'Pembayaran sedang menunggu penyelesaian.';
                },
                onError: () => {
                    this.message = 'Pembayaran gagal. Silakan coba lagi.';
                },
                onClose: () => {
                    this.message = 'Jendela pembayaran ditutup. Anda dapat melanjutkan kapan saja.';
                },
            });
        }
    }">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-medium uppercase tracking-wide text-slate-400">Pembayaran Booking</p>
            <h1 class="mt-1 font-[Barlow_Condensed] text-3xl font-semibold text-blue-900">Selesaikan pembayaran penuh</h1>
            <p class="mt-2 text-sm text-slate-500">Tiket akan diproses setelah pembayaran berhasil dikonfirmasi oleh Midtrans.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                <div>
                    <p class="text-xs uppercase tracking-wide text-slate-400">Booking</p>
                    <p class="font-[IBM_Plex_Mono] text-lg font-semibold text-slate-800">#{{ str_pad($booking->id, 5, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs uppercase tracking-wide text-slate-400">Total</p>
                    <p class="font-[IBM_Plex_Mono] text-xl font-bold text-blue-900">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</p>
                </div>
            </div>

            @if ($error)
                <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $error }}</div>
                <a href="{{ route('payments.show', $booking) }}" class="mt-5 inline-flex rounded-xl bg-blue-900 px-5 py-2.5 text-sm font-medium text-white">Coba Lagi</a>
            @elseif ($snapToken)
                <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    Pembayaran menggunakan Midtrans. Jangan menutup halaman sebelum memilih metode pembayaran.
                </div>
                <button type="button" @click="pay" class="mt-5 w-full rounded-xl bg-blue-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-800">
                    Bayar Sekarang
                </button>
                <p class="mt-3 text-center text-sm text-slate-500" x-text="message"></p>
            @endif

            <a href="{{ route('bookings.index') }}" class="mt-4 block text-center text-sm font-medium text-slate-500 hover:text-slate-700">Kembali ke booking</a>
        </div>
    </div>

    @if ($snapToken)
        <script src="{{ config('services.midtrans.snap_url') }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
    @endif
@endsection
