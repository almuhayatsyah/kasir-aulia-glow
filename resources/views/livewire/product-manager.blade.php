<div class="flex h-screen flex-col overflow-hidden bg-slate-50">

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
            <h1 class="text-lg font-bold tracking-tight text-slate-800">Manajemen Produk</h1>
            <p class="text-xs text-slate-400">Kelola data produk & stok</p>
        </div>

        <button
            wire:click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-pink-500 to-rose-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-pink-500/25 transition-all hover:shadow-lg hover:shadow-pink-500/30 active:scale-95"
        >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Produk
        </button>
    </header>

    {{-- ═══ SEARCH & FILTER BAR ═══ --}}
    <div class="flex shrink-0 items-center gap-3 border-b border-slate-100 bg-white px-6 py-3">
        {{-- Search --}}
        <div class="relative flex-1">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari nama produk atau barcode..."
                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2 pl-10 pr-4 text-sm text-slate-700 placeholder-slate-400 transition focus:border-pink-300 focus:bg-white focus:outline-none focus:ring-2 focus:ring-pink-100"
            >
        </div>

        {{-- Category Filter --}}
        <select
            wire:model.live="categoryFilter"
            class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-600 transition focus:border-pink-300 focus:outline-none focus:ring-2 focus:ring-pink-100"
        >
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
        </select>

        {{-- Product count --}}
        <div class="rounded-lg bg-slate-100 px-3 py-2 text-sm font-medium text-slate-500">
            {{ $products->total() }} produk
        </div>
    </div>

    {{-- ═══ TABLE ═══ --}}
    <div class="flex-1 overflow-y-auto">
        <table class="w-full">
            <thead class="sticky top-0 z-10 bg-slate-50">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                    <th class="px-6 py-3">
                        <button wire:click="sortBy('barcode')" class="flex items-center gap-1 hover:text-slate-600">
                            Barcode
                            @if($sortField === 'barcode')
                                <svg class="h-3 w-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3">
                        <button wire:click="sortBy('name')" class="flex items-center gap-1 hover:text-slate-600">
                            Nama Produk
                            @if($sortField === 'name')
                                <svg class="h-3 w-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3">Kategori</th>
                    <th class="px-6 py-3 text-right">
                        <button wire:click="sortBy('hpp_price')" class="ml-auto flex items-center gap-1 hover:text-slate-600">
                            HPP
                            @if($sortField === 'hpp_price')
                                <svg class="h-3 w-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-right">
                        <button wire:click="sortBy('sell_price')" class="ml-auto flex items-center gap-1 hover:text-slate-600">
                            Harga Jual
                            @if($sortField === 'sell_price')
                                <svg class="h-3 w-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-center">
                        <button wire:click="sortBy('stock')" class="flex items-center gap-1 hover:text-slate-600">
                            Stok
                            @if($sortField === 'stock')
                                <svg class="h-3 w-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3">
                        <button wire:click="sortBy('exp_date')" class="flex items-center gap-1 hover:text-slate-600">
                            Exp. Date
                            @if($sortField === 'exp_date')
                                <svg class="h-3 w-3 {{ $sortDirection === 'desc' ? 'rotate-180' : '' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"/></svg>
                            @endif
                        </button>
                    </th>
                    <th class="px-6 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @forelse($products as $product)
                    <tr class="group transition-colors hover:bg-slate-50/80" wire:key="product-{{ $product->id }}">
                        <td class="px-6 py-3">
                            <code class="rounded bg-slate-100 px-2 py-0.5 text-xs font-mono text-slate-600">{{ $product->barcode }}</code>
                        </td>
                        <td class="px-6 py-3">
                            <p class="font-semibold text-slate-700">{{ $product->name }}</p>
                        </td>
                        <td class="px-6 py-3">
                            @if($product->category)
                                <span class="inline-flex rounded-full bg-pink-50 px-2.5 py-0.5 text-xs font-medium text-pink-700">
                                    {{ $product->category }}
                                </span>
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-right text-sm text-slate-500">
                            Rp {{ number_format($product->hpp_price, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3 text-right text-sm font-semibold text-slate-700">
                            Rp {{ number_format($product->sell_price, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-3 text-center">
                            @if($product->stock <= 0)
                                <span class="inline-flex rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-bold text-red-600">Habis</span>
                            @elseif($product->stock <= 5)
                                <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-600">{{ $product->stock }}</span>
                            @else
                                <span class="text-sm font-medium text-slate-600">{{ $product->stock }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            @if($product->exp_date)
                                @if($product->exp_date->isPast())
                                    <span class="text-xs font-semibold text-red-500">{{ $product->exp_date->format('d/m/Y') }} ⚠️</span>
                                @elseif($product->exp_date->diffInMonths(now()) <= 3)
                                    <span class="text-xs font-medium text-amber-500">{{ $product->exp_date->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-xs text-slate-500">{{ $product->exp_date->format('d/m/Y') }}</span>
                                @endif
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <button
                                    wire:click="openEditModal({{ $product->id }})"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-blue-50 hover:text-blue-600 active:scale-95"
                                    title="Edit"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button
                                    wire:click="confirmDelete({{ $product->id }})"
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-red-50 hover:text-red-500 active:scale-95"
                                    title="Hapus"
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
                        <td colspan="8" class="px-6 py-16 text-center">
                            <svg class="mx-auto mb-4 h-16 w-16 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="text-sm font-medium text-slate-400">Tidak ada produk ditemukan</p>
                            <p class="mt-1 text-xs text-slate-300">Coba ubah kata kunci pencarian atau tambah produk baru</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ═══ PAGINATION ═══ --}}
    @if($products->hasPages())
        <div class="shrink-0 border-t border-slate-200 bg-white px-6 py-3">
            {{ $products->links() }}
        </div>
    @endif

    {{-- ═══ CREATE/EDIT MODAL ═══ --}}
    @if($showModal)
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
        >
            {{-- Modal Content --}}
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                class="w-full max-w-lg rounded-2xl bg-white shadow-2xl"
            >
                {{-- Modal Header --}}
                <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4">
                    <h3 class="text-lg font-bold text-slate-800">
                        {{ $editingProductId ? 'Edit Produk' : 'Tambah Produk Baru' }}
                    </h3>
                    <button wire:click="closeModal" class="flex h-8 w-8 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Modal Body --}}
                <form wire:submit="save" class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Barcode --}}
                        <div class="col-span-1">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Barcode <span class="text-red-400">*</span></label>
                            <input
                                type="text"
                                wire:model="barcode"
                                placeholder="Scan atau ketik barcode"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-pink-300 focus:outline-none focus:ring-2 focus:ring-pink-100 {{ $errors->has('barcode') ? 'border-red-300 bg-red-50' : '' }}"
                            >
                            @error('barcode') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Category --}}
                        <div class="col-span-1">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Kategori</label>
                            <input
                                type="text"
                                wire:model="category"
                                placeholder="Contoh: Skincare, Makeup"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-pink-300 focus:outline-none focus:ring-2 focus:ring-pink-100"
                            >
                        </div>

                        {{-- Name --}}
                        <div class="col-span-2">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Nama Produk <span class="text-red-400">*</span></label>
                            <input
                                type="text"
                                wire:model="name"
                                placeholder="Nama lengkap produk"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-pink-300 focus:outline-none focus:ring-2 focus:ring-pink-100 {{ $errors->has('name') ? 'border-red-300 bg-red-50' : '' }}"
                            >
                            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- HPP Price --}}
                        <div class="col-span-1">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Harga HPP (Rp) <span class="text-red-400">*</span></label>
                            <input
                                type="number"
                                wire:model="hpp_price"
                                placeholder="0"
                                min="0"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-pink-300 focus:outline-none focus:ring-2 focus:ring-pink-100 {{ $errors->has('hpp_price') ? 'border-red-300 bg-red-50' : '' }}"
                            >
                            @error('hpp_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Sell Price --}}
                        <div class="col-span-1">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Harga Jual (Rp) <span class="text-red-400">*</span></label>
                            <input
                                type="number"
                                wire:model="sell_price"
                                placeholder="0"
                                min="0"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-pink-300 focus:outline-none focus:ring-2 focus:ring-pink-100 {{ $errors->has('sell_price') ? 'border-red-300 bg-red-50' : '' }}"
                            >
                            @error('sell_price') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Stock --}}
                        <div class="col-span-1">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Stok <span class="text-red-400">*</span></label>
                            <input
                                type="number"
                                wire:model="stock"
                                placeholder="0"
                                min="0"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-pink-300 focus:outline-none focus:ring-2 focus:ring-pink-100 {{ $errors->has('stock') ? 'border-red-300 bg-red-50' : '' }}"
                            >
                            @error('stock') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                        </div>

                        {{-- Expiry Date --}}
                        <div class="col-span-1">
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-slate-500">Tanggal Kedaluwarsa</label>
                            <input
                                type="date"
                                wire:model="exp_date"
                                class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm transition focus:border-pink-300 focus:outline-none focus:ring-2 focus:ring-pink-100"
                            >
                        </div>

                        {{-- Margin Preview --}}
                        @if($hpp_price && $sell_price && is_numeric($hpp_price) && is_numeric($sell_price))
                            <div class="col-span-2 rounded-lg bg-slate-50 px-4 py-3">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-slate-500">Margin Keuntungan:</span>
                                    @php
                                        $margin = (int) $sell_price - (int) $hpp_price;
                                        $marginPercent = (int) $hpp_price > 0 ? round(($margin / (int) $hpp_price) * 100, 1) : 0;
                                    @endphp
                                    <span class="font-bold {{ $margin >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                        Rp {{ number_format($margin, 0, ',', '.') }}
                                        <span class="text-xs font-normal">({{ $marginPercent }}%)</span>
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Modal Footer --}}
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            wire:click="closeModal"
                            class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 active:scale-95"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex items-center gap-2 rounded-lg bg-gradient-to-r from-pink-500 to-rose-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-all hover:shadow-md active:scale-95 disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="save">
                                {{ $editingProductId ? 'Simpan Perubahan' : 'Tambah Produk' }}
                            </span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Menyimpan...
                            </span>
                        </button>
                    </div>
                </form>
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
            x-on:keydown.escape.window="$wire.closeDeleteModal()"
        >
            <div
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-on:click.outside="$wire.closeDeleteModal()"
                class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl"
            >
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                    </svg>
                </div>

                <h3 class="text-lg font-bold text-slate-800">Hapus Produk?</h3>
                <p class="mt-2 text-sm text-slate-500">
                    Produk <strong class="text-slate-700">"{{ $deletingProductName }}"</strong> akan dihapus permanen. Tindakan ini tidak bisa dibatalkan.
                </p>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <button
                        wire:click="closeDeleteModal"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 active:scale-95"
                    >
                        Batal
                    </button>
                    <button
                        wire:click="deleteProduct"
                        class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700 active:scale-95"
                    >
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
