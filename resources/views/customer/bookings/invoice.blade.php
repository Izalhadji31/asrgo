<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice Booking #{{ $booking->id }} — ASR GO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1a1a2e; font-size: 12px; line-height: 1.5; }
        .toolbar { position: fixed; top: 0; left: 0; right: 0; background: #1e3a8a; color: #fff; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; z-index: 10; }
        .toolbar button { background: #fff; color: #1e3a8a; border: 0; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .toolbar button:hover { background: #f1f5f9; }
        .toolbar a { color: #fff; text-decoration: none; margin-left: 16px; font-size: 12px; }
        .page { max-width: 210mm; margin: 60px auto 0; padding: 20px 24px; }
        .header { border-bottom: 3px solid #1e3a8a; padding-bottom: 12px; margin-bottom: 14px; display: flex; justify-content: space-between; align-items: flex-end; }
        .header h1 { font-size: 22px; color: #1e3a8a; letter-spacing: 0.5px; }
        .header h2 { font-size: 16px; margin-top: 2px; }
        .header p { color: #555; font-size: 11px; margin-top: 2px; }
        .header .inv-no { text-align: right; font-size: 11px; color: #444; }
        .header .inv-no strong { font-size: 15px; color: #1e3a8a; }
        .meta { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 11px; color: #444; }
        h3 { font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; color: #1e3a8a; margin: 18px 0 8px; border-left: 4px solid #E8A33D; padding-left: 8px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th { background: #1e3a8a; color: #fff; text-align: left; padding: 7px 10px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.3px; }
        td { padding: 7px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        .num { text-align: right; font-variant-numeric: tabular-nums; }
        .summary td { border: 1px solid #cbd5e1; background: #fff !important; }
        .summary .label { color: #64748b; font-size: 11px; }
        .summary .value { font-size: 15px; font-weight: 700; color: #1e3a8a; }
        .total td { background: #eef2ff !important; font-weight: 700; border-top: 2px solid #1e3a8a; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: 600; }
        .badge.paid { background: #dcfce7; color: #166534; }
        .badge.pending { background: #fef3c7; color: #92400e; }
        .badge.failed, .badge.expired { background: #fee2e2; color: #991b1b; }
        .info { font-size: 11px; color: #555; }
        .footer { margin-top: 28px; display: flex; justify-content: space-between; align-items: flex-end; font-size: 11px; color: #444; }
        .sign { text-align: center; width: 220px; }
        .sign .line { border-top: 1px solid #444; margin-top: 56px; padding-top: 6px; }
        @media print {
            .toolbar { display: none; }
            body { font-size: 11px; }
            .page { margin: 0; padding: 0; max-width: none; }
            @page { size: A4; margin: 14mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <span>Pratinjau cetak — Invoice Booking #{{ $booking->id }}</span>
        <span>
            <button onclick="window.print()">Cetak / Simpan PDF</button>
            <a href="{{ route('bookings.show', $booking) }}">Kembali</a>
        </span>
    </div>

    <div class="page">
        <div class="header">
            <div>
                <h1>ASR GO</h1>
                <h2>Invoice / Faktur Pembayaran</h2>
                <p>Layanan Rental & Travel — Flores, NTT</p>
            </div>
            <div class="inv-no">
                <div>No. Invoice</div>
                <strong>INV-{{ str_pad((string) $booking->id, 5, '0', STR_PAD_LEFT) }}/{{ $booking->created_at?->format('Y') }}</strong>
            </div>
        </div>

        <div class="meta">
            <span>Tanggal Invoice: {{ now()->translatedFormat('d F Y') }}</span>
            <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} — {{ Auth::user()->name }}</span>
        </div>

        <h3>Data Pemesan</h3>
        <table>
            <tbody>
                <tr>
                    <td class="info" style="width:25%">Nama</td>
                    <td><strong>{{ $booking->pelanggan?->name ?? '-' }}</strong></td>
                </tr>
                <tr>
                    <td class="info">Email</td>
                    <td>{{ $booking->pelanggan?->email ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="info">No. HP Kontak</td>
                    <td>{{ $booking->contact_hp ?? $booking->passengers->first()?->no_hp ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <h3>Detail Pesanan</h3>
        <table>
            <thead>
                <tr>
                    <th>Layanan</th>
                    <th>Kendaraan</th>
                    <th>Plat Nomor</th>
                    <th>Sopir</th>
                    <th>Tanggal</th>
                    <th>No. Tiket</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $booking->service_type === 'travel' ? 'Travel' : 'Rental' }}@if ($booking->service_type === 'travel')<br><span class="info">{{ $booking->origin }} → {{ $booking->destination }} ({{ $booking->session === 'pagi' ? 'Pagi' : 'Siang' }})</span>@endif</td>
                    <td>{{ $booking->vehicle?->nama ?? '-' }}</td>
                    <td>{{ $booking->vehicle?->plat_nomor ?? '-' }}</td>
                    <td>{{ $booking->sopir?->name ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($booking->tanggal_mulai)->translatedFormat('d M Y') }} s/d {{ \Carbon\Carbon::parse($booking->tanggal_selesai)->translatedFormat('d M Y') }}</td>
                    <td>{{ $booking->ticket_number ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        @if ($booking->service_type === 'travel' && $booking->passengers->count())
        <h3>Data Penumpang</h3>
        <table>
            <thead>
                <tr>
                    <th style="width:8%">No</th>
                    <th>Nama</th>
                    <th>No. HP</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($booking->passengers as $passenger)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $passenger->nama }}</td>
                    <td>{{ $passenger->no_hp }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <h3>Rincian Pembayaran</h3>
        <table class="summary">
            <tr>
                <td><div class="label">Status Pembayaran</div><div class="value">
                    @php
                        $paymentLabel = match ($booking->payment_status) {
                            'paid' => 'Lunas',
                            'pending' => 'Menunggu Pembayaran',
                            'failed' => 'Pembayaran Gagal',
                            'expired' => 'Kedaluwarsa',
                            default => 'Belum Dibayar',
                        };
                        $paymentBadge = match ($booking->payment_status) {
                            'paid' => 'paid',
                            'pending' => 'pending',
                            default => 'failed',
                        };
                    @endphp
                    <span class="badge {{ $paymentBadge }}">{{ $paymentLabel }}</span>
                </div></td>
                <td><div class="label">Skema</div><div class="value">{{ $booking->payment_scheme === 'dp' ? 'DP 30%' : 'Lunas Penuh' }}</div></td>
                <td><div class="label">Nominal Dibayar</div><div class="value">Rp {{ number_format($booking->payment_amount ?? 0, 0, ',', '.') }}</div></td>
            </tr>
        </table>

        <table>
            <tbody>
                <tr>
                    <td class="info" style="width:25%">Total Harga</td>
                    <td class="num"><strong>Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</strong></td>
                </tr>
                @if ($booking->payment_status === 'paid' && $booking->payment_scheme === 'dp')
                <tr>
                    <td class="info">Sisa Pembayaran (70%)</td>
                    <td class="num">Rp {{ number_format($booking->total_harga - (int) ($booking->payment_amount ?? 0), 0, ',', '.') }} <span class="info">— dikonfirmasi manual oleh admin</span></td>
                </tr>
                @endif
                <tr class="total">
                    <td>Total Pembayaran</td>
                    <td class="num">Rp {{ number_format($booking->payment_status === 'paid' ? $booking->total_harga : (int) ($booking->payment_amount ?? 0), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <div class="sign">
                <div>Mengetahui,</div>
                <div>Admin ASR GO</div>
                <div class="line"></div>
            </div>
            <div class="sign">
                <div>Hormat kami,</div>
                <div>{{ $booking->pelanggan?->name ?? 'Customer' }}</div>
                <div class="line"></div>
            </div>
        </div>
    </div>
</body>
</html>
