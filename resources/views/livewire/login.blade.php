<div class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-100 via-pink-50 to-rose-50 p-4">

    {{-- Login Card --}}
    <div class="w-full max-w-md">

        {{-- Logo & Title --}}
        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow-lg shadow-pink-500/10">
                <img src="{{ asset('img/logoaulia.png') }}" alt="Aulia Glow" class="h-14 w-14 object-contain">
            </div>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-800">Aulia Glow</h1>
            <p class="mt-1 text-sm text-slate-400">Masuk sebagai Owner untuk mengelola toko</p>
        </div>

        {{-- Card --}}
        <div class="rounded-2xl border border-slate-100 bg-white p-8 shadow-xl shadow-slate-200/50">

            {{-- Error Message --}}
            @if($errorMessage)
                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                    <div class="flex items-center gap-2">
                        <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        {{ $errorMessage }}
                    </div>
                </div>
            @endif

            <form wire:submit="authenticate">
                {{-- Email --}}
                <div class="mb-5">
                    <label for="email" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Email</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input
                            type="email"
                            id="email"
                            wire:model="email"
                            placeholder="owner@aulia.com"
                            autofocus
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm font-medium text-slate-700 placeholder-slate-400 transition-all duration-200 focus:border-pink-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-pink-100"
                        >
                    </div>
                    @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Password --}}
                <div class="mb-5">
                    <label for="password" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-500">Password</label>
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input
                            type="password"
                            id="password"
                            wire:model="password"
                            placeholder="••••••••"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 py-3 pl-11 pr-4 text-sm font-medium text-slate-700 placeholder-slate-400 transition-all duration-200 focus:border-pink-400 focus:bg-white focus:outline-none focus:ring-4 focus:ring-pink-100"
                        >
                    </div>
                    @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Remember Me --}}
                <div class="mb-6 flex items-center justify-between">
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-500">
                        <input
                            type="checkbox"
                            wire:model="remember"
                            class="h-4 w-4 rounded border-slate-300 text-pink-600 focus:ring-pink-500"
                        >
                        Ingat saya
                    </label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="w-full rounded-xl bg-gradient-to-r from-pink-500 to-rose-600 px-6 py-3.5 text-sm font-bold text-white shadow-lg shadow-pink-500/30 transition-all duration-200 hover:from-pink-600 hover:to-rose-700 hover:shadow-xl hover:shadow-pink-500/40 active:scale-[0.98] disabled:cursor-wait disabled:opacity-50"
                >
                    <span wire:loading.remove class="flex items-center justify-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        MASUK
                    </span>
                    <span wire:loading class="flex items-center justify-center gap-2">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        Memverifikasi...
                    </span>
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="mt-6 text-center text-xs text-slate-400">
            &copy; {{ date('Y') }} Aulia Glow &mdash; Cosmetic Store POS
        </p>
    </div>
</div>
