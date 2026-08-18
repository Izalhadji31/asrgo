<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keuangan ASR GO</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #1a1a2e; font-size: 12px; line-height: 1.5; }
        .toolbar { position: fixed; top: 0; left: 0; right: 0; background: #1e3a8a; color: #fff; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; z-index: 10; }
        .toolbar button { background: #fff; color: #1e3a8a; border: 0; padding: 8px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .toolbar button:hover { background: #f1f5f9; }
        .toolbar a { color: #fff; text-decoration: none; margin-left: 16px; font-size: 12px; }
        .page { max-width: 210mm; margin: 60px auto 0; padding: 20px 24px; }
        .header { border-bottom: 3px solid #1e3a8a; padding-bottom: 12px; margin-bottom: 14px; }
        .header h1 { font-size: 22px; color: #1e3a8a; letter-spacing: 0.5px; }
        .header h2 { font-size: 16px; margin-top: 2px; }
        .header p { color: #555; font-size: 11px; margin-top: 2px; }
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
        .footer { margin-top: 28px; display: flex; justify-content: space-between; align-items: flex-end; font-size: 11px; color: #444; }
        .sign { text-align: center; width: 220px; }
        .sign .line { border-top: 1px solid #444; margin-top: 56px; padding-top: 6px; }
        @media print {
            .toolbar { display: none; }
            body { font-size: 11px; }
            .page { margin: 0; padding: 0; max-width: none; }
            @page { size: A4; margin: 14mm; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <span>Pratinjau cetak — Laporan Keuangan</span>
        <span>
            <button onclick="window.print()">Cetak / Simpan PDF</button>
            <a href="{{ route('admin.reports.index') }}">Kembali</a>
        </span>
    </div>

    <div class="page">
        <div class="header">
            <h1>ASR GO</h1>
            <h2>Laporan Keuangan</h2>
            <p>Ringkasan pendapatan, payout, dan status pencairan sistem</p>
        </div>

        <div class="meta">
            <span>Periode: Seluruh transaksi hingga {{ now()->translatedFormat('d F Y') }}</span>
            <span>Dicetak: {{ now()->translatedFormat('d F Y, H:i') }} — {{ Auth::user()->name }}</span>
        </div>

        <h3>Ringkasan Pendapatan</h3>
        <table class="summary">
            <tr>
                <td><div class="label">Pendapatan Kotor</div><div class="value">Rp {{ number_format($stats['gross'], 0, ',', '.') }}</div></td>
                <td><div class="label">Pendapatan Platform</div><div class="value">Rp {{ number_format($stats['platform'], 0, ',', '.') }}</div></td>
                <td><div class="label">Pendapatan Mitra</div><div class="value">Rp {{ number_format($stats['mitra'], 0, ',', '.') }}</div></td>
                <td><div class="label">Pencairan Pending</div><div class="value">Rp {{ number_format($stats['pending'], 0, ',', '.') }}</div></td>
            </tr>
        </table>

        <h3>Rekap Bulan Ini & Status Pencairan</h3>
        <table class="summary">
            <tr>
                <td><div class="label">Gross Bulan Ini</div><div class="value">Rp {{ number_format($stats['monthly_gross'], 0, ',', '.') }}</div></td>
                <td><div class="label">Platform Bulan Ini</div><div class="value">Rp {{ number_format($stats['monthly_platform'], 0, ',', '.') }}</div></td>
                <td><div class="label">Mitra Bulan Ini</div><div class="value">Rp {{ number_format($stats['monthly_mitra'], 0, ',', '.') }}</div></td>
                <td><div class="label">Split Default</div><div class="value">Platform {{ $globalShare?->persen_platform ?? 0 }}% / Mitra {{ $globalShare?->persen_mitra ?? 0 }}%</div></td>
            </tr>
        </table>

        <h3>Riwayat Payout ({{ $payouts->count() }} transaksi)</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Booking</th>
                    <th>Tanggal</th>
                    <th>Mitra</th>
                    <th>Customer</th>
                    <th>Unit</th>
                    <th class="num">Platform</th>
                    <th class="num">Mitra</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($payouts as $i => $payout)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>#{{ $payout->booking_id }}</td>
                        <td>{{ $payout->created_at?->translatedFormat('d M Y') ?? '-' }}</td>
                        <td>{{ $payout->mitra?->name ?? '-' }}</td>
                        <td>{{ $payout->booking?->pelanggan?->name ?? '-' }}</td>
                        <td>{{ $payout->booking?->vehicle?->nama ?? '-' }}</td>
                        <td class="num">Rp {{ number_format($payout->jumlah_platform, 0, ',', '.') }}</td>
                        <td class="num">Rp {{ number_format($payout->jumlah_mitra, 0, ',', '.') }}</td>
                        <td><span class="badge {{ $payout->status_pencairan === 'paid' ? 'paid' : 'pending' }}">{{ $payout->status_pencairan === 'paid' ? 'Paid' : 'Pending' }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9">Belum ada payout yang terbentuk.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total">
                    <td colspan="6">Total</td>
                    <td class="num">Rp {{ number_format($stats['platform'], 0, ',', '.') }}</td>
                    <td class="num">Rp {{ number_format($stats['mitra'], 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <div class="footer">
            <div>Dokumen ini dicetak otomatis dari sistem ASR GO.<br>Bukan merupakan bukti transaksi resmi.</div>
            <div class="sign">
                <div>Mengetahui,</div>
                <div>Admin ASR GO</div>
                <div class="line">{{ Auth::user()->name }}</div>
            </div>
        </div>
    </div>
</body>
</html>
