<div
    x-data="{}"
    x-on:reprint-receipt.window="$nextTick(() => window.print())"
    class="flex h-screen flex-col overflow-hidden bg-slate-50"
>
    {{-- ═══ HEADER ═══ --}}
    <header class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-6 py-3 shadow-sm">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-slate-800">Riwayat Transaksi</h1>
            <p class="text-xs text-slate-400">Histori penjualan & detail struk</p>
        </div>
    </header>

    {{-- ═══ SUMMARY CARDS ═══ --}}
    <div class="grid shrink-0 grid-cols-3 gap-4 border-b border-slate-100 bg-white px-6 py-4">
        <div class="rounded-xl border border-slate-100 bg-gradient-to-br from-blue-50 to-indigo-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-blue-400">Total Transaksi</p>
            <p class="mt-1 text-2xl font-extrabold text-blue-700">{{ number_format($summary['total_transaksi']) }}</p>
            <p class="text-xs text-blue-400">Periode terpilih</p>
        </div>
        <div class="rounded-xl border border-slate-100 bg-gradient-to-br from-emerald-50 to-green-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-400">Total Penjualan</p>
            <p class="mt-1 text-2xl font-extrabold text-emerald-700">Rp {{ number_format($summary['total_penjualan'], 0, ',', '.') }}</p>
            <p class="text-xs text-emerald-400">Omzet kotor</p>
        </div>
        <div class="rounded-xl border border-slate-100 bg-gradient-to-br from-pink-50 to-rose-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-pink-400">Total Profit</p>
            <p class="mt-1 text-2xl font-extrabold text-pink-700">Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}</p>
            <p class="text-xs text-pink-400">Keuntungan bersih</p>
        </div>
    </div>

    {{-- ═══ FILTER BAR ═══ --}}
    <div class="flex shrink-0 flex-col gap-3 border-b border-slate-100 bg-white px-6 py-3">
        {{-- Preset Buttons --}}
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Periode:</span>
            @foreach([
                'hari' => 'Hari Ini',
                'minggu' => 'Minggu Ini',
                'bulan' => 'Bulan Ini',
                'tahun' => 'Tahun Ini',
            ] as $key => $label)
                <button
                    wire:click="setPreset('{{ $key }}')"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium transition active:scale-95
                        {{ $activePreset === $key
                            ? 'bg-gradient-to-r from-pink-500 to-rose-600 text-white shadow-sm shadow-pink-500/25'
                            : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-pink-600 hover:border-pink-200'
                        }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- Date Inputs & Search --}}
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
            <div class="relative flex-1">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Cari No. Transaksi..."
                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm text-slate-600 placeholder-slate-400 transition focus:border-pink-300 focus:outline-none focus:ring-2 focus:ring-pink-100"
                >
            </div>
        </div>
    </div>

    {{-- ═══ TRANSACTION TABLE ═══ --}}
    <div class="flex-1 overflow-y-auto">
        <table class="w-full">
            <thead class="sticky top-0 z-10 bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                    <th class="px-6 py-3">No. Transaksi</th>
                    <th class="px-6 py-3">Tanggal & Waktu</th>
                    <th class="px-6 py-3 text-right">Total Penjualan</th>
                    <th class="px-6 py-3 text-right">HPP</th>
                    <th class="px-6 py-3 text-right">Profit</th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($transactions as $trx)
                    <tr class="group transition-colors hover:bg-slate-50/80" wire:key="trx-{{ $trx->id }}">
                        <td class="px-6 py-3">
                            <span class="rounded-md bg-slate-100 px-2 py-1 text-sm font-bold text-slate-700">#{{ str_pad((string) $trx->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="px-6 py-3">
                            <p class="text-sm font-medium text-slate-700">{{ $trx->created_at->format('d/m/Y') }}</p>
                            <p class="text-xs text-slate-400">{{ $trx->created_at->format('H:i:s') }}</p>
                        </td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-slate-700">
                            Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3 text-right text-sm text-slate-500">
                            Rp {{ number_format($trx->total_hpp, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3 text-right">
                            <span class="text-sm font-bold {{ $trx->total_profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                Rp {{ number_format($trx->total_profit, 0, ',', '.') }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button
                                    wire:click="viewDetail({{ $trx->id }})"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 active:scale-95"
                                    title="Lihat Detail"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <button
                                    wire:click="reprintReceipt({{ $trx->id }})"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-pink-50 hover:text-pink-600 active:scale-95"
                                    title="Cetak Ulang Struk"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center">
                            <svg class="mx-auto mb-4 h-16 w-16 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <p class="text-sm font-medium text-slate-400">Belum ada transaksi</p>
                            <p class="mt-1 text-xs text-slate-300">Transaksi akan muncul setelah checkout di POS</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ═══ PAGINATION ═══ --}}
    @if($transactions->hasPages())
        <div class="shrink-0 border-t border-slate-200 bg-white px-6 py-3">
            {{ $transactions->links() }}
        </div>
    @endif

    {{-- ═══ DETAIL MODAL ═══ --}}
    @if($showDetail)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            x-on:keydown.escape.window="$wire.closeDetail()"
        >
            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-on:click.outside="$wire.closeDetail()"
                class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl"
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">
                            Transaksi #{{ str_pad((string) ($selectedTransactionId ?? 0), 5, '0', STR_PAD_LEFT) }}
                        </h3>
                        <p class="text-sm text-slate-400">{{ $selectedDate }} — {{ $selectedTime }}</p>
                    </div>
                    <button wire:click="closeDetail" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Detail Items --}}
                <div class="max-h-80 overflow-y-auto px-6 py-4">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-slate-100 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                                <th class="pb-2">Produk</th>
                                <th class="pb-2 text-right">Harga</th>
                                <th class="pb-2 text-center">Qty</th>
                                <th class="pb-2 text-right">Subtotal</th>
                                <th class="pb-2 text-right">Profit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @foreach($selectedDetails as $detail)
                                <tr>
                                    <td class="py-2.5">
                                        <p class="text-sm font-medium text-slate-700">{{ $detail['product_name'] }}</p>
                                        <p class="text-xs text-slate-400">{{ $detail['barcode'] }}</p>
                                    </td>
                                    <td class="py-2.5 text-right text-sm text-slate-600">
                                        Rp {{ number_format($detail['price'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 text-center text-sm font-medium text-slate-700">{{ $detail['qty'] }}</td>
                                    <td class="py-2.5 text-right text-sm font-semibold text-slate-700">
                                        Rp {{ number_format($detail['subtotal'], 0, ',', '.') }}
                                    </td>
                                    <td class="py-2.5 text-right text-sm font-bold {{ $detail['profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        Rp {{ number_format($detail['profit'], 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Modal Footer - Totals --}}
                <div class="border-t border-slate-100 px-6 py-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="rounded-lg bg-slate-50 p-3 text-center">
                            <p class="text-xs font-semibold uppercase text-slate-400">Total Penjualan</p>
                            <p class="mt-1 text-lg font-extrabold text-slate-800">Rp {{ number_format($selectedTotalAmount, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-lg bg-slate-50 p-3 text-center">
                            <p class="text-xs font-semibold uppercase text-slate-400">Total HPP</p>
                            <p class="mt-1 text-lg font-extrabold text-slate-500">Rp {{ number_format($selectedTotalHpp, 0, ',', '.') }}</p>
                        </div>
                        <div class="rounded-lg bg-emerald-50 p-3 text-center">
                            <p class="text-xs font-semibold uppercase text-emerald-400">Profit</p>
                            <p class="mt-1 text-lg font-extrabold text-emerald-700">Rp {{ number_format($selectedTotalProfit, 0, ',', '.') }}</p>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between rounded-xl bg-slate-50 p-3 text-sm text-slate-600 border border-slate-100">
                        <div>
                            <span>Uang Diterima: <strong>Rp {{ number_format($selectedCashReceived, 0, ',', '.') }}</strong></span>
                        </div>
                        <div>
                            <span>Kembalian: <strong class="text-emerald-600">Rp {{ number_format($selectedCashChange, 0, ',', '.') }}</strong></span>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end gap-3">
                        <button
                            wire:click="closeDetail"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 active:scale-95"
                        >
                            Tutup
                        </button>
                        <button
                            wire:click="reprintReceipt({{ $selectedTransactionId }})"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-pink-500 to-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:shadow-md active:scale-95"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Cetak Ulang Struk
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ PRINT-ONLY RECEIPT ═══ --}}
    @if($showDetail)
        <div class="hidden print:block" id="receipt-area">
            <div style="width: 280px; font-family: 'Courier New', monospace; font-size: 12px; padding: 10px;">
                <div style="text-align: center; margin-bottom: 10px;">
                    <strong style="font-size: 16px;">AULIA GLOW</strong><br>
                    <span>Cosmetic Store</span><br>
                    <span>================================</span>
                </div>

                <div style="margin-bottom: 5px;">
                    <span>No: #{{ str_pad((string) ($selectedTransactionId ?? 0), 5, '0', STR_PAD_LEFT) }}</span><br>
                    <span>{{ $selectedDate }} {{ $selectedTime }}</span>
                </div>

                <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; margin-bottom: 5px;">
                    @foreach($selectedDetails as $detail)
                        <div style="margin-bottom: 4px;">
                            <div style="font-weight: bold;">{{ $detail['product_name'] }}</div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>{{ $detail['qty'] }} x Rp {{ number_format($detail['price'], 0, ',', '.') }}</span>
                                <span>Rp {{ number_format($detail['subtotal'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-top: 5px;">
                    <span>TOTAL</span>
                    <span>Rp {{ number_format($selectedTotalAmount, 0, ',', '.') }}</span>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 3px; font-size: 11px;">
                    <span>Bayar (Cash)</span>
                    <span>Rp {{ number_format($selectedCashReceived, 0, ',', '.') }}</span>
                </div>

                <div style="display: flex; justify-content: space-between; margin-top: 3px; font-size: 11px;">
                    <span>Kembali</span>
                    <span>Rp {{ number_format($selectedCashChange, 0, ',', '.') }}</span>
                </div>

                <div style="text-align: center; margin-top: 15px; font-size: 11px;">
                    <span>================================</span><br>
                    <span>Terima Kasih!</span><br>
                    <span>Semoga Anda Semakin Cantik ✨</span>
                </div>
            </div>
        </div>
    @endif
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
