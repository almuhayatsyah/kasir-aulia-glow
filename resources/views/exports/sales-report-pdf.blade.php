<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan - Aulia Glow</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            background: #fff;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #ec4899, #f43f5e);
            color: white;
            padding: 18px 24px;
            margin-bottom: 16px;
        }
        .header h1 { font-size: 18px; font-weight: 700; }
        .header p { font-size: 10px; opacity: 0.85; margin-top: 3px; }
        .header-meta { margin-top: 10px; font-size: 10px; opacity: 0.9; }

        /* Summary Cards */
        .section { padding: 0 24px; margin-bottom: 16px; }
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #be185d;
            border-left: 3px solid #ec4899;
            padding-left: 8px;
            margin-bottom: 10px;
        }

        .cards {
            width: 100%;
            border-collapse: collapse;
        }
        .cards td {
            width: 25%;
            padding: 10px 12px;
            border: 1px solid #fce7f3;
            border-radius: 8px;
            vertical-align: middle;
        }
        .card-label {
            font-size: 8.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #94a3b8;
        }
        .card-value {
            font-size: 14px;
            font-weight: 700;
            margin-top: 3px;
            color: #1e293b;
        }
        .card-value.profit { color: #10b981; }
        .card-value.hpp { color: #64748b; }

        /* Table */
        table.data {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        table.data thead tr { background: #fdf2f8; }
        table.data thead th {
            padding: 7px 10px;
            text-align: left;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #be185d;
            border-bottom: 2px solid #fbcfe8;
        }
        table.data thead th.right { text-align: right; }
        table.data tbody tr { border-bottom: 1px solid #f1f5f9; }
        table.data tbody tr:nth-child(even) { background: #fafafa; }
        table.data tbody td { padding: 6px 10px; }
        table.data tbody td.right { text-align: right; }
        .profit-pos { color: #10b981; font-weight: 700; }
        .profit-neg { color: #ef4444; font-weight: 700; }
        .rank-badge {
            display: inline-block;
            background: #fce7f3;
            color: #be185d;
            font-weight: 700;
            font-size: 8px;
            padding: 1px 5px;
            border-radius: 4px;
        }

        /* Progress bar */
        .bar-bg { background: #f1f5f9; border-radius: 4px; height: 6px; }
        .bar-fill { background: linear-gradient(to right, #ec4899, #f43f5e); border-radius: 4px; height: 6px; }

        /* Footer */
        .footer {
            margin: 16px 24px 0;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 8.5px;
            color: #94a3b8;
        }

        .two-col { display: flex; gap: 16px; }
        .two-col > div { flex: 1; }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h1>🌸 Laporan Penjualan — Aulia Glow</h1>
        <p>Rekap omzet, HPP, dan profit bersih</p>
        <div class="header-meta">
            Periode: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}
            &nbsp;&nbsp;|&nbsp;&nbsp;
            Dicetak: {{ now()->format('d M Y, H:i') }} WIB
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="section">
        <div class="section-title">Ringkasan Keuangan</div>
        <table class="cards">
            <tr>
                <td style="background:#fdf2f8; margin-right:8px;">
                    <div class="card-label">Total Omzet</div>
                    <div class="card-value">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</div>
                </td>
                <td style="background:#f0fdf4; margin:0 4px;">
                    <div class="card-label">Total Profit</div>
                    <div class="card-value profit">Rp {{ number_format($summary['profit'], 0, ',', '.') }}</div>
                </td>
                <td style="background:#f8fafc; margin:0 4px;">
                    <div class="card-label">Total HPP</div>
                    <div class="card-value hpp">Rp {{ number_format($summary['hpp'], 0, ',', '.') }}</div>
                </td>
                <td style="background:#fff7ed;">
                    <div class="card-label">Jml Transaksi</div>
                    <div class="card-value" style="color:#ea580c;">{{ number_format($summary['transaksi'], 0, ',', '.') }}</div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Top Produk & Daily side-by-side --}}
    @if(count($topProducts) > 0)
    <div class="section">
        <div class="section-title">Produk Terlaris</div>
        @php $maxQty = collect($topProducts)->max('qty') ?: 1; @endphp
        <table class="data">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Produk</th>
                    <th class="right">Terjual (pcs)</th>
                    <th class="right">Omzet (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topProducts as $i => $product)
                <tr>
                    <td><span class="rank-badge">{{ $i + 1 }}</span></td>
                    <td>{{ $product['name'] }}</td>
                    <td class="right">{{ number_format($product['qty'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($product['subtotal'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Breakdown Harian --}}
    @if(count($dailyBreakdown) > 0)
    <div class="section">
        <div class="section-title">Breakdown Harian</div>
        <table class="data">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th class="right">Jml Transaksi</th>
                    <th class="right">Omzet (Rp)</th>
                    <th class="right">HPP (Rp)</th>
                    <th class="right">Profit (Rp)</th>
                    <th class="right">Rata-rata/Trx</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dailyBreakdown as $row)
                <tr>
                    <td>{{ $row['date'] }}</td>
                    <td class="right">{{ $row['count'] }}</td>
                    <td class="right">Rp {{ number_format($row['omzet'], 0, ',', '.') }}</td>
                    <td class="right" style="color:#64748b;">Rp {{ number_format($row['omzet'] - $row['profit'], 0, ',', '.') }}</td>
                    <td class="right {{ $row['profit'] >= 0 ? 'profit-pos' : 'profit-neg' }}">
                        Rp {{ number_format($row['profit'], 0, ',', '.') }}
                    </td>
                    <td class="right" style="color:#64748b;">
                        Rp {{ $row['count'] > 0 ? number_format((int)($row['omzet'] / $row['count']), 0, ',', '.') : '0' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <span>Aulia Glow — Sistem Kasir Digital</span>
        <span>auliaglow.my.id &nbsp;|&nbsp; {{ now()->format('d/m/Y H:i') }}</span>
    </div>

</body>
</html>
