<x-guest-layout>
    <div class="mb-5">
        <h1 class="text-lg font-bold text-slate-900">Masuk ke Dashboard Toko</h1>
        <p class="text-xs text-slate-600 mt-1">Gunakan akun Owner atau Kasir yang terdaftar untuk mengelola pelanggan &amp; transaksi.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                   placeholder="owner@umkm.com"
                   class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi</label>
            <input id="password" type="password" name="password" required autocomplete="current-password"
                   placeholder="••••••••"
                   class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
        </div>

        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" name="remember"
                       class="rounded border-slate-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                <span class="ms-2 text-xs text-slate-600">Ingat saya</span>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs font-medium text-indigo-600 hover:text-indigo-700">
                    Lupa sandi?
                </a>
            @endif
        </div>

        <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-sm shadow-indigo-100/50 hover:bg-indigo-700 active:scale-[0.99] transition">
            <i data-lucide="log-in" class="w-4 h-4"></i>
            Masuk Sekarang
        </button>
    </form>

    @if(Route::has('register'))
        <div class="mt-5 pt-4 border-t border-slate-100 text-center text-xs text-slate-600">
            Belum mendaftarkan toko UMKM Anda?
            <a href="{{ route('register') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Daftar Toko Baru</a>
        </div>
    @endif

    @if(app()->environment('local'))
        <div class="mt-4 rounded-xl bg-indigo-50/70 border border-indigo-100 p-3 text-[11px] text-slate-700">
            <div class="font-semibold text-indigo-700 mb-1">Akun Demo (Seeder Lokal):</div>
            <div>Owner: <code class="font-mono text-indigo-800">owner@umkm.com</code> / <code class="font-mono text-indigo-800">password</code></div>
            <div>Kasir: <code class="font-mono text-indigo-800">kasir@umkm.com</code> / <code class="font-mono text-indigo-800">password</code></div>
        </div>
    @endif
</x-guest-layout>
