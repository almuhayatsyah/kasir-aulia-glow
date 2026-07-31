<div
    x-data="{}"
    x-on:reprint-receipt.window="$nextTick(() => window.print())"
    class="flex h-screen flex-col overflow-hidden bg-slate-50"
>

    {{-- ═══ TOAST NOTIFICATION ═══ --}}
    @if($toastMessage)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => { show = false; $wire.dismissToast() }, 5000)"
            x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 -translate-y-6 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-6 scale-95"
            class="fixed top-6 left-1/2 z-[100] -translate-x-1/2 flex items-center gap-4 rounded-2xl border-2 px-6 py-4 shadow-2xl min-w-[360px] {{ $toastType === 'success' ? 'bg-emerald-50 border-emerald-400 text-emerald-800' : 'bg-red-50 border-red-400 text-red-800' }}"
        >
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full {{ $toastType === 'success' ? 'bg-emerald-500' : 'bg-red-500' }}">
                @if($toastType === 'success')
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" /></svg>
                @else
                    <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" /></svg>
                @endif
            </div>
            <div class="flex-1">
                <p class="text-base font-bold">{{ $toastType === 'success' ? 'Berhasil!' : 'Gagal!' }}</p>
                <p class="text-sm font-medium opacity-80">{{ $toastMessage }}</p>
            </div>
            <button x-on:click="show = false; $wire.dismissToast()" class="shrink-0 rounded-xl p-1.5 transition {{ $toastType === 'success' ? 'hover:bg-emerald-200' : 'hover:bg-red-200' }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    @endif
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
                    <th class="px-6 py-3">Nama Produk</th>
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
                            <div class="max-w-xs space-y-0.5">
                                @foreach($trx->details->take(2) as $detail)
                                    <p class="truncate text-xs font-semibold text-slate-700">
                                        {{ $detail->product?->name ?? 'Produk dihapus' }}
                                        <span class="text-slate-400 font-normal">({{ $detail->qty }}x)</span>
                                    </p>
                                @endforeach
                                @if($trx->details->count() > 2)
                                    <p class="text-[11px] font-medium text-pink-600">+{{ $trx->details->count() - 2 }} item lainnya</p>
                                @endif
                            </div>
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
                                <button
                                    wire:click="confirmDelete({{ $trx->id }})"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-600 active:scale-95"
                                    title="Hapus Transaksi"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
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

    {{-- ═══ DELETE CONFIRMATION MODAL ═══ --}}
    @if($showDeleteModal)
        <div
            x-data="{ open: true }"
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
        >
            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
            >
                {{-- Header --}}
                <div class="mb-5 flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-100">
                        <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Pilih Jenis Penghapusan</h3>
                        <p class="mt-1 text-sm text-slate-500">Transaksi <strong class="text-slate-700">{{ $deletingTransactionCode }}</strong></p>
                    </div>
                </div>

                {{-- Option 1: Retur/Tukar --}}
                <div class="mb-3 rounded-xl p-4" style="border: 2px solid #6ee7b7; background-color: #ecfdf5;">
                    <div class="mb-2 flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full" style="background-color: #10b981;">
                            <svg class="h-4 w-4" fill="none" stroke="white" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <p class="font-bold" style="color: #065f46;">Retur / Tukar Produk</p>
                    </div>
                    <p class="mb-3 text-xs" style="color: #047857;">
                        Transaksi dihapus dan <strong>stok produk dikembalikan ke rak</strong>. Gunakan ini jika customer ingin menukar produk. Setelah ini, buat transaksi baru dengan produk pengganti.
                    </p>
                    <button
                        wire:click="deleteTransaction"
                        wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-bold text-white shadow-sm transition active:scale-95 disabled:opacity-50"
                        style="background-color: #10b981;"
                        onmouseover="this.style.backgroundColor='#059669'"
                        onmouseout="this.style.backgroundColor='#10b981'"
                    >
                        <span wire:loading.remove wire:target="deleteTransaction">✅ Retur & Kembalikan Stok</span>
                        <span wire:loading wire:target="deleteTransaction" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Memproses...
                        </span>
                    </button>
                </div>

                {{-- Option 2: Hapus Permanen --}}
                <div class="mb-5 rounded-xl p-4" style="border: 2px solid #fca5a5; background-color: #fff1f2;">
                    <div class="mb-2 flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full" style="background-color: #ef4444;">
                            <svg class="h-4 w-4" fill="none" stroke="white" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </div>
                        <p class="font-bold" style="color: #991b1b;">Hapus Permanen</p>
                    </div>
                    <p class="mb-3 text-xs" style="color: #b91c1c;">
                        Transaksi dihapus dan <strong>stok produk TIDAK dikembalikan</strong>. Gunakan ini jika data transaksi salah input atau produk memang sudah tidak ada.
                    </p>
                    <button
                        wire:click="deleteTransactionPermanent"
                        wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg px-4 py-2.5 text-sm font-bold text-white shadow-sm transition active:scale-95 disabled:opacity-50"
                        style="background-color: #dc2626;"
                        onmouseover="this.style.backgroundColor='#b91c1c'"
                        onmouseout="this.style.backgroundColor='#dc2626'"
                    >
                        <span wire:loading.remove wire:target="deleteTransactionPermanent">🗑️ Hapus Permanen (Stok Tetap)</span>
                        <span wire:loading wire:target="deleteTransactionPermanent" class="flex items-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Menghapus...
                        </span>
                    </button>
                </div>

                {{-- Cancel --}}
                <button
                    wire:click="closeDeleteModal"
                    class="w-full rounded-xl border border-slate-200 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50 active:scale-95"
                >
                    Batal
                </button>
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
