<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Aulia Glow' }}</title>
    
    {{-- PWA Meta Tags --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#ec4899">
    <link rel="apple-touch-icon" href="{{ asset('img/logoaulia.png') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100 antialiased">
    <div class="flex h-screen overflow-hidden">

        {{-- ═══ SIDEBAR ═══ --}}
        <aside class="flex w-[72px] shrink-0 flex-col items-center border-r border-slate-200 bg-white py-4 shadow-sm print:hidden">

            {{-- Logo --}}
            <a href="{{ route('pos') }}" class="mb-6">
                <img src="{{ Vite::asset('resources/img/logoaulia.png') }}" alt="Aulia Glow" class="h-11 w-11 rounded-xl object-contain shadow-md">
            </a>

            {{-- Nav Items --}}
            <nav class="flex flex-1 flex-col items-center gap-1">
                @php
                    $navItems = [
                        ['route' => 'pos', 'label' => 'Kasir', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                        ['route' => 'produk', 'label' => 'Produk', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                        ['route' => 'transaksi', 'label' => 'Transaksi', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                        ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z'],
                        ['route' => 'laporan', 'label' => 'Laporan', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    @php $isActive = request()->routeIs($item['route']); @endphp
                    <a
                        href="{{ route($item['route']) }}"
                        class="group relative flex h-12 w-12 flex-col items-center justify-center rounded-xl transition-all duration-200
                            {{ $isActive
                                ? 'bg-gradient-to-br from-pink-500 to-rose-600 text-white shadow-md shadow-pink-500/25'
                                : 'text-slate-400 hover:bg-slate-50 hover:text-pink-600'
                            }}"
                        title="{{ $item['label'] }}"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                        </svg>

                        {{-- Tooltip --}}
                        <div class="pointer-events-none absolute left-full ml-3 hidden rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-medium text-white shadow-lg group-hover:block">
                            {{ $item['label'] }}
                            <div class="absolute -left-1 top-1/2 h-2 w-2 -translate-y-1/2 rotate-45 bg-slate-800"></div>
                        </div>
                    </a>
                    <span class="mb-1 text-[10px] font-medium {{ $isActive ? 'text-pink-600' : 'text-slate-400' }}">{{ $item['label'] }}</span>
                @endforeach
            </nav>

            {{-- Bottom: Logout + Clock --}}
            <div class="mt-auto flex flex-col items-center gap-2">
                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="group relative flex h-10 w-10 items-center justify-center rounded-xl text-slate-400 transition-all duration-200 hover:bg-red-50 hover:text-red-500"
                        title="Logout"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        {{-- Tooltip --}}
                        <div class="pointer-events-none absolute left-full ml-3 hidden rounded-lg bg-slate-800 px-3 py-1.5 text-xs font-medium text-white shadow-lg group-hover:block whitespace-nowrap">
                            Logout
                            <div class="absolute -left-1 top-1/2 h-2 w-2 -translate-y-1/2 rotate-45 bg-slate-800"></div>
                        </div>
                    </button>
                </form>

                {{-- Clock --}}
                <div class="text-center"
                     x-data="{ time: '' }"
                     x-init="setInterval(() => { time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }, 1000)">
                    <p class="text-xs font-bold text-slate-600" x-text="time"></p>
                    <p class="text-[9px] text-slate-400" x-text="new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })"></p>
                </div>
            </div>
        </aside>

        {{-- ═══ MAIN CONTENT ═══ --}}
        <main class="flex-1 overflow-hidden">
            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
    
    {{-- Register Service Worker --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').then((registration) => {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, (err) => {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
</body>
</html>
