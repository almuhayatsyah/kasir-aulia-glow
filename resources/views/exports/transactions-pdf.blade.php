<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi - Aulia Glow</title>
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
            padding: 20px 24px;
            margin-bottom: 16px;
            border-radius: 0 0 8px 8px;
        }
        .header h1 { font-size: 20px; font-weight: 700; letter-spacing: -0.5px; }
        .header p { font-size: 11px; opacity: 0.85; margin-top: 4px; }
        .header .meta { margin-top: 12px; display: flex; gap: 20px; font-size: 10px; }
        .header .meta span { background: rgba(255,255,255,0.2); padding: 3px 10px; border-radius: 20px; }

        /* Summary Cards */
        .summary {
            display: flex;
            gap: 10px;
            margin: 0 24px 16px;
        }
        .card {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            text-align: center;
        }
        .card .label { font-size: 9px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; }
        .card .value { font-size: 14px; font-weight: 700; margin-top: 4px; color: #1e293b; }
        .card.profit .value { color: #10b981; }

        /* Table */
        .table-wrapper { padding: 0 24px; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }
        thead tr {
            background: #fdf2f8;
        }
        thead th {
            padding: 8px 10px;
            text-align: left;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #be185d;
            border-bottom: 2px solid #fbcfe8;
        }
        thead th.right { text-align: right; }
        thead th.center { text-align: center; }

        tbody tr { border-bottom: 1px solid #f1f5f9; }
        tbody tr:hover { background: #fdf2f8; }
        tbody tr.group-start { border-top: 2px solid #fbcfe8; }

        td { padding: 7px 10px; vertical-align: middle; }
        td.right { text-align: right; }
        td.center { text-align: center; }

        .trx-badge {
            background: #f1f5f9;
            border-radius: 4px;
            padding: 2px 7px;
            font-weight: 700;
            font-size: 9px;
            color: #334155;
            white-space: nowrap;
        }
        .product-name { font-weight: 600; color: #1e293b; }
        .product-meta { color: #94a3b8; font-size: 8.5px; margin-top: 1px; }
        .profit-pos { color: #10b981; font-weight: 700; }
        .profit-neg { color: #ef4444; font-weight: 700; }

        /* Footer */
        .footer {
            margin: 20px 24px 0;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h1>🌸 Laporan Transaksi — Aulia Glow</h1>
        <p>Ringkasan penjualan produk kecantikan</p>
        <div class="meta">
            @if($dateFrom || $dateTo)
                <span>Periode: {{ $dateFrom ? \Carbon\Carbon::parse($dateFrom)->format('d M Y') : '—' }} s/d {{ $dateTo ? \Carbon\Carbon::parse($dateTo)->format('d M Y') : '—' }}</span>
            @else
                <span>Semua Periode</span>
            @endif
            <span>Dicetak: {{ now()->format('d M Y, H:i') }} WIB</span>
            <span>Total: {{ $transactions->count() }} transaksi</span>
        </div>
    </div>

    {{-- Summary --}}
    <div class="summary">
        <div class="card">
            <div class="label">Total Transaksi</div>
            <div class="value">{{ $transactions->count() }}</div>
        </div>
        <div class="card">
            <div class="label">Total Penjualan</div>
            <div class="value">Rp {{ number_format($transactions->sum('total_amount'), 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="label">Total HPP</div>
            <div class="value">Rp {{ number_format($transactions->sum('total_hpp'), 0, ',', '.') }}</div>
        </div>
        <div class="card profit">
            <div class="label">Total Profit</div>
            <div class="value">Rp {{ number_format($transactions->sum('total_profit'), 0, ',', '.') }}</div>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>No. Trx</th>
                    <th>Produk</th>
                    <th class="right">Qty</th>
                    <th class="right">Harga/pcs</th>
                    <th class="right">Subtotal</th>
                    <th>Tanggal</th>
                    <th class="right">Total Trx</th>
                    <th class="right">HPP</th>
                    <th class="right">Profit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transactions as $trx)
                    @foreach($trx->details as $i => $detail)
                        <tr class="{{ $i === 0 ? 'group-start' : '' }}">
                            @if($i === 0)
                                <td rowspan="{{ $trx->details->count() }}">
                                    <span class="trx-badge">#{{ str_pad((string) $trx->id, 5, '0', STR_PAD_LEFT) }}</span>
                                </td>
                            @endif
                            <td>
                                <div class="product-name">{{ $detail->product?->name ?? 'Produk dihapus' }}</div>
                            </td>
                            <td class="right">{{ $detail->qty }}x</td>
                            <td class="right">Rp {{ number_format($detail->price, 0, ',', '.') }}</td>
                            <td class="right">Rp {{ number_format($detail->price * $detail->qty, 0, ',', '.') }}</td>
                            @if($i === 0)
                                <td rowspan="{{ $trx->details->count() }}">
                                    {{ $trx->created_at->format('d/m/Y') }}<br>
                                    <span style="color:#94a3b8;">{{ $trx->created_at->format('H:i') }}</span>
                                </td>
                                <td class="right" rowspan="{{ $trx->details->count() }}">
                                    Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                                </td>
                                <td class="right" rowspan="{{ $trx->details->count() }}" style="color:#64748b;">
                                    Rp {{ number_format($trx->total_hpp, 0, ',', '.') }}
                                </td>
                                <td class="right {{ $trx->total_profit >= 0 ? 'profit-pos' : 'profit-neg' }}" rowspan="{{ $trx->details->count() }}">
                                    Rp {{ number_format($trx->total_profit, 0, ',', '.') }}
                                </td>
                            @endif
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Footer --}}
    <div class="footer">
        <span>Aulia Glow — Sistem Kasir Digital</span>
        <span>auliaglow.my.id</span>
    </div>

</body>
</html>
