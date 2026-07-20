<div
    x-data="{
        showReceipt: false,
        showPaymentModal: @entangle('showPaymentModal'),
        receiptData: { total: 0, received: 0, change: 0, items: [] }
    }"
    x-on:checkout-success.window="receiptData = $event.detail; showReceipt = true; $nextTick(() => { window.print(); showReceipt = false; })"
    class="flex h-screen flex-col overflow-hidden bg-slate-50"
>
    {{-- ═══ HEADER ═══ --}}
    <header class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-6 py-3 shadow-sm">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-slate-800">Transaksi Baru</h1>
            <p class="text-xs text-slate-400">Kasir / Point of Sales</p>
        </div>

        <div class="flex items-center gap-4">
            <div class="rounded-lg bg-slate-100 px-4 py-2 text-sm font-medium text-slate-600"
                 x-data="{ time: '' }"
                 x-init="setInterval(() => { time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) }, 1000)">
                <span x-text="time"></span>
            </div>
            <div class="text-right">
                <p class="text-xs text-slate-400" x-text="new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })"></p>
            </div>
        </div>
    </header>

    {{-- ═══ MAIN CONTENT ═══ --}}
    {{-- ═══ MAIN CONTENT ═══ --}}
    <main class="flex min-h-0 flex-1">

        {{-- ─── LEFT: Catalog of Products (60%) ─── --}}
        <section class="flex w-[60%] flex-col border-r border-slate-200 bg-white">
            {{-- Search & Category Filter --}}
            <div class="border-b border-slate-100 p-4 space-y-3 shrink-0">
                {{-- Search & Scan Input --}}
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <svg class="h-5 w-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="barcode-input"
                        wire:model.live.debounce.300ms="search"
                        wire:keydown.enter="processSearch"
                        x-ref="barcodeInput"
                        x-init="$el.focus()"
                        x-on:scan-barcode-done.window="$nextTick(() => $el.focus())"
                        x-on:blur="if (!showPaymentModal) setTimeout(() => $el.focus(), 100)"
                        autofocus
                        autocomplete="off"
                        placeholder="Scan barcode ATAU ketik nama produk di sini..."
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-12 pr-4 text-base font-medium text-slate-700 placeholder-slate-400 transition-all duration-200 focus:border-pink-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-pink-100"
                    >
                </div>

                {{-- Category Pills --}}
                <div class="flex flex-wrap gap-2 pt-1">
                    <button
                        wire:click="selectCategory('')"
                        class="rounded-full px-4 py-1 text-xs font-semibold tracking-wide transition active:scale-95
                            {{ $selectedCategory === ''
                                ? 'bg-gradient-to-r from-pink-500 to-rose-600 text-white shadow-sm'
                                : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                            }}"
                    >
                        Semua Kategori
                    </button>
                    @foreach($categories as $cat)
                        <button
                            wire:click="selectCategory('{{ $cat }}')"
                            class="rounded-full px-4 py-1 text-xs font-semibold tracking-wide transition active:scale-95
                                {{ $selectedCategory === $cat
                                    ? 'bg-gradient-to-r from-pink-500 to-rose-600 text-white shadow-sm'
                                    : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
                                }}"
                        >
                            {{ $cat }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Catalog Grid --}}
            <div class="flex-1 overflow-y-auto p-4 bg-slate-50/50">
                @if($products->isEmpty())
                    <div class="flex h-full flex-col items-center justify-center text-slate-300">
                        <svg class="mb-4 h-16 w-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <p class="text-base font-semibold">Produk Kosmetik Tidak Ditemukan</p>
                        <p class="text-xs">Coba cari dengan kata kunci lain</p>
                    </div>
                @else
                    <div class="grid grid-cols-3 gap-4">
                        @foreach($products as $product)
                            @php
                                $isExpired = $product->exp_date !== null && $product->exp_date->isBefore(\Illuminate\Support\Carbon::today());
                                $isOutOfStock = $product->stock <= 0;
                                $isDisabled = $isOutOfStock || $isExpired;
                            @endphp
                            <button
                                wire:click="addProductById({{ $product->id }})"
                                @if($isDisabled) disabled @endif
                                class="flex flex-col justify-between rounded-2xl border text-left p-4 bg-white shadow-sm hover:shadow-md hover:border-pink-300 transition-all duration-200 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:border-slate-200 disabled:hover:shadow-none"
                            >
                                <div class="w-full">
                                    {{-- Category badge --}}
                                    <div class="flex justify-between items-start gap-1">
                                        <span class="inline-flex rounded-full bg-pink-50 px-2.5 py-0.5 text-[10px] font-semibold text-pink-700">
                                            {{ $product->category ?: 'General' }}
                                        </span>
                                        {{-- Stock indicator --}}
                                        @if($isOutOfStock)
                                            <span class="text-[10px] font-bold text-red-600">Habis</span>
                                        @elseif($isExpired)
                                            <span class="text-[10px] font-bold text-red-600">Expired ⚠️</span>
                                        @else
                                            <span class="text-[10px] font-medium text-slate-400">Stok: {{ $product->stock }}</span>
                                        @endif
                                    </div>
                                    <h4 class="mt-2 text-sm font-bold text-slate-800 line-clamp-2 leading-snug">{{ $product->name }}</h4>
                                    <code class="text-[10px] font-mono text-slate-400 block mt-1">{{ $product->barcode }}</code>
                                </div>
                                <div class="mt-4 flex items-center justify-between w-full">
                                    <span class="text-sm font-extrabold text-pink-600">Rp {{ number_format($product->sell_price, 0, ',', '.') }}</span>
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-pink-50 text-pink-600 group-hover:bg-pink-100">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </span>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>

        {{-- ─── RIGHT: Cart, Barcode Input + Checkout (40%) ─── --}}
        <aside class="flex w-[40%] flex-col bg-slate-50">


            {{-- Flash Messages --}}
            @if($flashMessage)
                <div
                    x-data="{ show: true }"
                    x-init="setTimeout(() => { show = false; $wire.clearFlash() }, 3000)"
                    x-show="show"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="mx-4 mt-3 rounded-lg px-4 py-2.5 text-xs font-semibold {{ $flashType === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}"
                >
                    <div class="flex items-center gap-2">
                        @if($flashType === 'error')
                            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        @else
                            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        @endif
                        <span>{{ $flashMessage }}</span>
                    </div>
                </div>
            @endif

            {{-- Cart List --}}
            <div class="flex-1 overflow-y-auto p-4">
                @if(empty($cart))
                    <div class="flex h-full flex-col items-center justify-center text-slate-300">
                        <svg class="mb-3 h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z" />
                        </svg>
                        <p class="text-sm font-semibold">Keranjang Belanja Kosong</p>
                        <p class="text-xs text-slate-400">Pilih produk di kiri atau scan barcode</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($cart as $productId => $item)
                            <div class="flex items-center justify-between rounded-xl bg-white p-3 shadow-sm border border-slate-100" wire:key="cart-item-{{ $productId }}">
                                <div class="min-w-0 flex-1">
                                    <h4 class="truncate text-sm font-bold text-slate-700">{{ $item['name'] }}</h4>
                                    <p class="text-xs text-pink-600 font-bold mt-0.5">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                                    <span class="text-[10px] font-mono text-slate-400">{{ $item['barcode'] }}</span>
                                </div>
                                <div class="flex items-center gap-3 ml-3 shrink-0">
                                    {{-- Qty controls --}}
                                    <div class="flex items-center gap-1.5">
                                        <button
                                            wire:click="decrementQty({{ $productId }})"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition active:scale-95"
                                        >
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4" />
                                            </svg>
                                        </button>
                                        <span class="w-6 text-center text-sm font-extrabold text-slate-700">{{ $item['qty'] }}</span>
                                        <button
                                            wire:click="incrementQty({{ $productId }})"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-200 transition active:scale-95"
                                        >
                                            <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="text-right w-20">
                                        <p class="text-sm font-extrabold text-slate-800">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                                    </div>
                                    <button
                                        wire:click="removeItem({{ $productId }})"
                                        class="text-slate-300 hover:text-red-500 transition active:scale-95"
                                        title="Hapus"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Checkout Footer --}}
            <div class="border-t border-slate-200 bg-white p-5">
                {{-- Total --}}
                <div class="mb-4 flex items-end justify-between">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Bayar</p>
                        <p class="text-sm text-slate-500">{{ count($cart) }} item unik</p>
                    </div>
                    <p class="text-3xl font-extrabold tracking-tight text-slate-800">
                        Rp {{ number_format($totalAmount, 0, ',', '.') }}
                    </p>
                </div>

                {{-- Checkout Button ─── --}}
                <button
                    wire:click="openPaymentModal"
                    @if(empty($cart)) disabled @endif
                    class="w-full rounded-xl bg-gradient-to-r from-pink-500 to-rose-600 px-6 py-4 text-base font-bold text-white shadow-lg shadow-pink-500/30 transition-all duration-200 hover:from-pink-600 hover:to-rose-700 hover:shadow-xl hover:shadow-pink-500/40 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-40 disabled:shadow-none"
                >
                    <span class="flex items-center justify-center gap-2">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        LANJUT PEMBAYARAN
                    </span>
                </button>
            </div>
        </aside>
    </main>

    {{-- ═══ PAYMENT MODAL ═══ --}}
    @if($showPaymentModal)
        <div
            x-data="{}"
            x-on:focus-cash-received.window="$nextTick(() => { $refs.cashReceivedInput.focus(); $refs.cashReceivedInput.select(); })"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            x-on:keydown.escape.window="$wire.closePaymentModal()"
        >
            <div
                x-on:click.outside="$wire.closePaymentModal()"
                class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl"
            >
                <div class="mb-4 flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-lg font-bold text-slate-800">Metode Pembayaran (Cash)</h3>
                    <button wire:click="closePaymentModal" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Bill Details --}}
                <div class="mb-4 rounded-xl bg-pink-50 p-4 text-center">
                    <p class="text-xs font-semibold uppercase tracking-wider text-pink-700">Total Tagihan</p>
                    <p class="mt-1 text-3xl font-black text-pink-600">Rp {{ number_format($totalAmount, 0, ',', '.') }}</p>
                </div>

                {{-- Cash Received Input --}}
                <div class="mb-4">
                    <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Uang Diterima (Rp)</label>
                    <input
                        type="text"
                        wire:model.live="cashReceivedInput"
                        x-ref="cashReceivedInput"
                        placeholder="Masukkan nominal cash..."
                        class="w-full rounded-xl border border-slate-200 py-3 px-4 text-xl font-bold text-slate-800 text-right focus:border-pink-400 focus:outline-none focus:ring-4 focus:ring-pink-100"
                    >
                </div>

                {{-- Quick Cash Presets --}}
                <div class="mb-5">
                    <p class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">Pilihan Uang Pas/Cepat</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            wire:click="selectQuickCash({{ $totalAmount }})"
                            class="rounded-lg border border-slate-200 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 active:scale-95 transition"
                        >
                            Uang Pas
                        </button>
                        @foreach([10000, 20000, 50000, 100000] as $amount)
                            @if($amount > $totalAmount)
                                <button
                                    wire:click="selectQuickCash({{ $amount }})"
                                    class="rounded-lg border border-slate-200 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 active:scale-95 transition"
                                >
                                    Rp {{ number_format($amount, 0, ',', '.') }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Change Calculation --}}
                <div class="mb-6 rounded-xl bg-slate-50 p-4 flex items-center justify-between border border-slate-100">
                    <div>
                        <p class="text-xs font-semibold text-slate-500">Kembalian</p>
                        <p class="text-xs text-slate-400">Uang kembali ke pelanggan</p>
                    </div>
                    <p class="text-2xl font-black {{ $cashReceived >= $totalAmount ? 'text-emerald-600' : 'text-red-500' }}">
                        Rp {{ number_format($changeAmount, 0, ',', '.') }}
                    </p>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-3">
                    <button
                        wire:click="closePaymentModal"
                        class="flex-1 rounded-xl border border-slate-200 py-3 text-sm font-semibold text-slate-600 hover:bg-slate-50 active:scale-95 transition"
                    >
                        Batal
                    </button>
                    <button
                        wire:click="checkout"
                        wire:loading.attr="disabled"
                        @if($cashReceived < $totalAmount) disabled @endif
                        class="flex-1 rounded-xl bg-gradient-to-r from-pink-500 to-rose-600 py-3 text-sm font-bold text-white shadow-md shadow-pink-500/25 hover:shadow-lg active:scale-95 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="checkout">
                            PROSES & PRINT STRUK
                        </span>
                        <span wire:loading wire:target="checkout" class="flex items-center justify-center gap-2">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ PRINT-ONLY RECEIPT ═══ --}}
    <div class="hidden print:block" id="receipt-area">
        <div style="width: 280px; font-family: 'Courier New', monospace; font-size: 12px; padding: 10px;">
                <strong style="font-size: 16px;">AULIA GLOW</strong><br>
                <span>Cosmetic Store</span><br>
                <span>================================</span>
            </div>

            <div style="margin-bottom: 5px;">
                <span x-text="new Date().toLocaleDateString('id-ID')"></span> &mdash;
                <span x-text="new Date().toLocaleTimeString('id-ID')"></span>
            </div>

            <div style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding: 5px 0; margin-bottom: 5px;">
                <template x-for="(item, index) in receiptData.items" :key="index">
                    <div style="margin-bottom: 4px;">
                        <div x-text="item.name" style="font-weight: bold;"></div>
                        <div style="display: flex; justify-content: space-between;">
                            <span x-text="item.qty + ' x Rp ' + item.price.toLocaleString('id-ID')"></span>
                            <span x-text="'Rp ' + item.subtotal.toLocaleString('id-ID')"></span>
                        </div>
                    </div>
                </template>
            </div>

            <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px; margin-top: 5px;">
                <span>TOTAL</span>
                <span x-text="'Rp ' + receiptData.total.toLocaleString('id-ID')"></span>
            </div>

            <div style="display: flex; justify-content: space-between; margin-top: 3px; font-size: 11px;">
                <span>Bayar (Cash)</span>
                <span x-text="'Rp ' + receiptData.received.toLocaleString('id-ID')"></span>
            </div>

            <div style="display: flex; justify-content: space-between; margin-top: 3px; font-size: 11px;">
                <span>Kembali</span>
                <span x-text="'Rp ' + receiptData.change.toLocaleString('id-ID')"></span>
            </div>

            <div style="text-align: center; margin-top: 15px; font-size: 11px;">
                <span>================================</span><br>
                <span>Terima Kasih!</span><br>
                <span>Semoga Anda Semakin Cantik ✨</span>
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
