<div class="flex h-screen flex-col overflow-hidden bg-slate-50">

    {{-- ═══ HEADER ═══ --}}
    <header class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-6 py-3 shadow-sm">
        <div>
            <h1 class="text-lg font-bold tracking-tight text-slate-800">Dashboard</h1>
            <p class="text-xs text-slate-400">Ringkasan bisnis Aulia Glow</p>
        </div>
    </header>

    {{-- ═══ MAIN SCROLLABLE CONTENT ═══ --}}
    <main class="flex-1 overflow-y-auto p-6">

        {{-- ─── Period Buttons ─── --}}
        <div class="mb-6 flex items-center gap-2">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Periode:</span>
            @foreach([
                'hari' => 'Hari Ini',
                'minggu' => 'Minggu Ini',
                'bulan' => 'Bulan Ini',
                'tahun' => 'Tahun Ini',
            ] as $key => $label)
                <button
                    wire:click="setPeriod('{{ $key }}')"
                    class="rounded-lg px-3 py-1.5 text-sm font-medium transition active:scale-95
                        {{ $period === $key
                            ? 'bg-gradient-to-r from-pink-500 to-rose-600 text-white shadow-sm shadow-pink-500/25'
                            : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 hover:text-pink-600 hover:border-pink-200'
                        }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- ─── Summary Cards ─── --}}
        <div class="mb-6 grid grid-cols-4 gap-4">
            {{-- Omzet --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 shadow-sm">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Omzet</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-800">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</p>
            </div>

            {{-- Profit --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-pink-400 to-rose-600 shadow-sm">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Profit</p>
                <p class="mt-1 text-2xl font-extrabold {{ $summary['profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">Rp {{ number_format($summary['profit'], 0, ',', '.') }}</p>
            </div>

            {{-- Transaksi --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-400 to-indigo-600 shadow-sm">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Jumlah Transaksi</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-800">{{ number_format($summary['transaksi']) }}</p>
            </div>

            {{-- Rata-rata --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 shadow-sm">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                    </svg>
                </div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Rata-rata / Transaksi</p>
                <p class="mt-1 text-2xl font-extrabold text-slate-800">Rp {{ number_format($summary['rata_rata'], 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- ─── Chart + Top Products Row ─── --}}
        <div class="mb-6 grid grid-cols-3 gap-4">

            {{-- Sales Chart (7 Hari Terakhir) --}}
            <div class="col-span-2 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-bold text-slate-700">📊 Penjualan 7 Hari Terakhir</h3>
                @php
                    $maxOmzet = max(array_column($dailySales, 'omzet')) ?: 1;
                @endphp
                <div class="flex items-end gap-2" style="height: 180px;">
                    @foreach($dailySales as $day)
                        <div class="group relative flex flex-1 flex-col items-center justify-end" style="height: 100%;">
                            {{-- Tooltip --}}
                            <div class="pointer-events-none absolute -top-2 z-10 hidden rounded-lg bg-slate-800 px-3 py-2 text-xs text-white shadow-lg group-hover:block">
                                <p class="font-bold">{{ $day['label'] }}</p>
                                <p>Omzet: Rp {{ number_format($day['omzet'], 0, ',', '.') }}</p>
                                <p>Profit: Rp {{ number_format($day['profit'], 0, ',', '.') }}</p>
                            </div>
                            {{-- Bar --}}
                            <div
                                class="w-full rounded-t-lg bg-gradient-to-t from-pink-500 to-rose-400 transition-all duration-500 hover:from-pink-600 hover:to-rose-500"
                                style="height: {{ $maxOmzet > 0 ? max(($day['omzet'] / $maxOmzet) * 100, 2) : 2 }}%; min-height: 4px;"
                            ></div>
                            {{-- Label --}}
                            <p class="mt-2 text-center text-[10px] font-medium text-slate-400">{{ \Illuminate\Support\Str::before($day['label'], ',') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Top Products --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-bold text-slate-700">🏆 Produk Terlaris</h3>
                @if(empty($topProducts))
                    <p class="py-8 text-center text-sm text-slate-300">Belum ada data</p>
                @else
                    <div class="space-y-3">
                        @foreach($topProducts as $index => $product)
                            <div class="flex items-center gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold
                                    {{ $index === 0 ? 'bg-amber-100 text-amber-700' : ($index === 1 ? 'bg-slate-100 text-slate-600' : ($index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-slate-50 text-slate-400')) }}">
                                    {{ $index + 1 }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-slate-700">{{ $product['name'] }}</p>
                                    <p class="text-xs text-slate-400">{{ $product['qty'] }} terjual</p>
                                </div>
                                <p class="text-xs font-semibold text-slate-600">Rp {{ number_format($product['revenue'], 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- ─── Alerts Row ─── --}}
        <div class="grid grid-cols-3 gap-4">

            {{-- Inventory Stats --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h3 class="mb-4 text-sm font-bold text-slate-700">📦 Inventaris</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Total Produk</span>
                        <span class="text-lg font-bold text-slate-800">{{ number_format($inventoryStats['total_produk']) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Total Stok</span>
                        <span class="text-lg font-bold text-slate-800">{{ number_format($inventoryStats['total_stok']) }}</span>
                    </div>
                    <hr class="border-slate-100">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Stok Menipis</span>
                        <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-sm font-bold text-amber-700">{{ $lowStockProducts->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-500">Kedaluwarsa</span>
                        <span class="rounded-full bg-red-100 px-2.5 py-0.5 text-sm font-bold text-red-700">{{ $expiredProducts->count() }}</span>
                    </div>
                </div>
            </div>

            {{-- Low Stock Alert --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-bold text-amber-600">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    Stok Menipis (≤ 5)
                </h3>
                @if($lowStockProducts->isEmpty())
                    <p class="py-4 text-center text-sm text-slate-300">Semua stok aman 👍</p>
                @else
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($lowStockProducts as $product)
                            <div class="flex items-center justify-between rounded-lg bg-amber-50 px-3 py-2">
                                <p class="truncate text-sm font-medium text-slate-700">{{ $product->name }}</p>
                                <span class="ml-2 shrink-0 rounded-full px-2 py-0.5 text-xs font-bold
                                    {{ $product->stock <= 0 ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $product->stock <= 0 ? 'Habis' : 'Sisa ' . $product->stock }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Expired Products --}}
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <h3 class="mb-4 flex items-center gap-2 text-sm font-bold text-red-600">
                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                    Produk Kedaluwarsa
                </h3>
                @if($expiredProducts->isEmpty())
                    <p class="py-4 text-center text-sm text-slate-300">Tidak ada produk expired ✅</p>
                @else
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($expiredProducts as $product)
                            <div class="flex items-center justify-between rounded-lg bg-red-50 px-3 py-2">
                                <p class="truncate text-sm font-medium text-slate-700">{{ $product->name }}</p>
                                <span class="ml-2 shrink-0 text-xs font-semibold text-red-600">{{ $product->exp_date->format('d/m/Y') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>
