<div
    x-data="{}"
    x-on:print-report.window="$nextTick(() => window.print())"
    class="flex h-screen flex-col overflow-hidden bg-slate-50"
>
    {{-- ═══ HEADER ═══ --}}
    <header class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-6 py-3 shadow-sm">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-slate-800">Laporan Penjualan</h1>
            <p class="text-xs text-slate-400">Rekap omzet, HPP, & profit bersih</p>
        </div>

        <div class="flex items-center gap-2">
            {{-- Export Excel --}}
            <a
                href="{{ route('export.laporan.excel', array_filter(['dari' => $dateFrom, 'sampai' => $dateTo])) }}"
                target="_blank"
                class="inline-flex items-center gap-1.5 rounded-xl border px-4 py-2.5 text-sm font-semibold transition active:scale-95"
                style="border-color: #6ee7b7; background-color: #ecfdf5; color: #065f46;"
                onmouseover="this.style.backgroundColor='#d1fae5'"
                onmouseout="this.style.backgroundColor='#ecfdf5'"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Excel
            </a>

            {{-- Export PDF --}}
            <a
                href="{{ route('export.laporan.pdf', array_filter(['dari' => $dateFrom, 'sampai' => $dateTo])) }}"
                target="_blank"
                class="inline-flex items-center gap-1.5 rounded-xl border px-4 py-2.5 text-sm font-semibold transition active:scale-95"
                style="border-color: #fca5a5; background-color: #fff1f2; color: #991b1b;"
                onmouseover="this.style.backgroundColor='#ffe4e6'"
                onmouseout="this.style.backgroundColor='#fff1f2'"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                PDF
            </a>

            {{-- Print --}}
            <button
                x-on:click="$dispatch('print-report')"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-pink-500 to-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-pink-500/25 transition-all hover:shadow-lg hover:shadow-pink-500/30 active:scale-95"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak Rekap (Thermal)
            </button>
        </div>
    </header>

    {{-- ═══ MAIN SCROLLABLE CONTENT ═══ --}}
    <main class="flex-1 overflow-y-auto p-6">

        {{-- Filters Section --}}
        <div class="mb-6 flex shrink-0 flex-col gap-3 rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-4">
                {{-- Presets --}}
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Periode Cepat:</span>
                    @foreach([
                        'hari' => 'Hari Ini',
                        'minggu' => 'Minggu Ini',
                        'bulan' => 'Bulan Ini',
                        'tahun' => 'Tahun Ini',
                    ] as $key => $label)
                        <button
                            wire:click="setPreset('{{ $key }}')"
                            class="rounded-lg px-3 py-1.5 text-sm font-medium transition active:scale-95 border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-pink-600 hover:border-pink-200"
                        >
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- Date Inputs --}}
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Dari</label>
                        <input
                            type="date"
                            wire:model.live="dateFrom"
                            class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 transition focus:border-pink-300 focus:outline-none focus:ring-2 focus:ring-pink-100"
                        >
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sampai</label>
                        <input
                            type="date"
                            wire:model.live="dateTo"
                            class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 transition focus:border-pink-300 focus:outline-none focus:ring-2 focus:ring-pink-100"
                        >
                    </div>
                </div>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="mb-6 grid grid-cols-4 gap-4">
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Omzet</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-800">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-400">Pendapatan kotor</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total HPP</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-500">Rp {{ number_format($summary['hpp'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-400">Modal produk terjual</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm bg-gradient-to-br from-white to-pink-50/20">
                <p class="text-xs font-semibold uppercase tracking-wider text-pink-500">Untung Bersih</p>
                <p class="mt-1 text-2xl font-extrabold text-pink-600">Rp {{ number_format($summary['profit'], 0, ',', '.') }}</p>
                <p class="text-[10px] text-slate-400">Margin bersih</p>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Transaksi</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ number_format($summary['transaksi']) }}</p>
                <p class="text-[10px] text-slate-400">Rata-rata: Rp {{ number_format($summary['rata_rata'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- Grid Breakdown --}}
        <div class="grid grid-cols-3 gap-6">

            {{-- Daily Breakdown Table --}}
            <div class="col-span-2 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-bold text-slate-700">📅 Rincian Penjualan Harian</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr class="border-b border-slate-100 text-xs font-semibold uppercase text-slate-400">
                                <th class="pb-3">Tanggal</th>
                                <th class="pb-3 text-center">Trx</th>
                                <th class="pb-3 text-right">Omzet</th>
                                <th class="pb-3 text-right">Profit Bersih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($dailyBreakdown as $row)
                                <tr>
                                    <td class="py-3 font-medium text-slate-700">{{ $row['date'] }}</td>
                                    <td class="py-3 text-center">{{ $row['count'] }}</td>
                                    <td class="py-3 text-right font-semibold text-slate-800">Rp {{ number_format($row['omzet'], 0, ',', '.') }}</td>
                                    <td class="py-3 text-right font-bold text-emerald-600">Rp {{ number_format($row['profit'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-slate-300">Tidak ada transaksi pada periode ini</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Top Selling Products --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-bold text-slate-700">🏆 Produk Terlaris Periode Ini</h3>
                @if(empty($topProducts))
                    <p class="py-12 text-center text-sm text-slate-300">Belum ada penjualan</p>
                @else
                    <div class="space-y-4">
                        @foreach($topProducts as $index => $prod)
                            <div class="flex items-center gap-3">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold
                                    {{ $index === 0 ? 'bg-amber-100 text-amber-700' : ($index === 1 ? 'bg-slate-100 text-slate-600' : 'bg-slate-50 text-slate-400') }}">
                                    {{ $index + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-xs font-semibold text-slate-700">{{ $prod['name'] }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $prod['qty'] }} pcs terjual</p>
                                </div>
                                <p class="text-xs font-bold text-slate-700">Rp {{ number_format($prod['subtotal'], 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </main>

    {{-- ═══ PRINT-ONLY THERMAL REPORT SUMMARY ═══ --}}
    <div class="hidden print:block" id="receipt-area">
        <div style="width: 280px; font-family: 'Courier New', monospace; font-size: 12px; padding: 10px;">
            <div style="text-align: center; margin-bottom: 10px;">
                <strong style="font-size: 14px;">LAPORAN PENJUALAN</strong><br>
                <span style="font-size: 11px;">AULIA GLOW</span><br>
                <span>================================</span>
            </div>

            <div style="font-size: 11px; margin-bottom: 8px;">
                <span>Dari   : {{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d/m/Y') }}</span><br>
                <span>Sampai : {{ \Illuminate\Support\Carbon::parse($dateTo)->format('d/m/Y') }}</span><br>
                <span>Dicetak: {{ now()->format('d/m/Y H:i') }}</span>
            </div>

            <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; font-size: 11px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Transaksi:</span>
                    <strong>{{ $summary['transaksi'] }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Omzet kotor:</span>
                    <strong>Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 3px;">
                    <span>Total HPP:</span>
                    <strong>Rp {{ number_format($summary['hpp'], 0, ',', '.') }}</strong>
                </div>
                <div style="display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 3px;">
                    <span>Profit Bersih:</span>
                    <strong>Rp {{ number_format($summary['profit'], 0, ',', '.') }}</strong>
                </div>
            </div>

            @if(!empty($topProducts))
                <div style="margin-top: 10px;">
                    <div style="font-weight: bold; text-align: center; margin-bottom: 5px;">PRODUK TERLARIS</div>
                    @foreach($topProducts as $p)
                        <div style="display: flex; justify-content: space-between; font-size: 10px; margin-bottom: 2px;">
                            <span style="max-width: 170px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $p['name'] }}</span>
                            <span>{{ $p['qty'] }} pcs</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div style="text-align: center; margin-top: 20px; font-size: 10px;">
                <span>================================</span><br>
                <span>Aulia Glow &copy; {{ date('Y') }}</span>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        body * {
            visibility: hidden !important;
        }
        #receipt-area, #receipt-area * {
            visibility: visible !important;
        }
        #receipt-area {
            position: absolute;
            left: 0;
            top: 0;
        }
    }
</style>
@endpush
