<x-guest-layout>
    <div class="mb-5">
        <h1 class="text-lg font-bold text-slate-900">Daftarkan Toko UMKM Anda</h1>
        <p class="text-xs text-slate-600 mt-1">Buat workspace CRM multi-tenant gratis untuk mengelola pelanggan, poin loyalitas, dan broadcast email.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label for="store_name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Toko / Usaha</label>
            <input id="store_name" type="text" name="store_name" value="{{ old('store_name') }}" required autofocus
                   placeholder="Contoh: CleanShoe & Care Studio"
                   class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('store_name')" class="mt-1.5" />
        </div>

        <div>
            <label for="business_type" class="block text-xs font-semibold text-slate-700 mb-1">Jenis Layanan / Industri</label>
            <select id="business_type" name="business_type" required
                    class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">-- Pilih Jenis Usaha --</option>
                @foreach(['Cuci Sepatu & Tas', 'Barbershop & Salon', 'Laundry Kiloan & Satuan', 'Bengkel Kendaraan', 'Klinik Kecantikan', 'Jasa Lainnya'] as $type)
                    <option value="{{ $type }}" @selected(old('business_type') === $type)>{{ $type }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('business_type')" class="mt-1.5" />
        </div>

        <div>
            <label for="name" class="block text-xs font-semibold text-slate-700 mb-1">Nama Pemilik (Owner)</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                   class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold text-slate-700 mb-1">Email Owner</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
                   class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label for="password" class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi</label>
                <input id="password" type="password" name="password" required autocomplete="new-password"
                       class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            <div>
                <label for="password_confirmation" class="block text-xs font-semibold text-slate-700 mb-1">Ulangi Sandi</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
            </div>
        </div>

        <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-sm shadow-indigo-100/50 hover:bg-indigo-700 active:scale-[0.99] transition">
            <i data-lucide="sparkles" class="w-4 h-4"></i>
            Buat Akun &amp; Mulai Sekarang
        </button>

        <div class="text-center text-xs text-slate-600 pt-2">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-indigo-600 hover:text-indigo-700">Masuk di sini</a>
        </div>
    </form>
</x-guest-layout>
