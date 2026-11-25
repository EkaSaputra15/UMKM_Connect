<x-app-layout>
    <div class="space-y-6 max-w-3xl">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Pengaturan Profil Toko &amp; SMTP Email</h1>
            <p class="text-xs text-slate-600 mt-1">Atur identitas toko, aturan konversi poin loyalitas, dan pengirim email untuk nota digital serta broadcast.</p>
        </div>

        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Store Profile --}}
            <section class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-sm shadow-indigo-100/50 space-y-4">
                <h2 class="text-base font-bold text-slate-900">Profil Toko</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Toko *</label>
                        <input type="text" name="name" value="{{ old('name', $store->name) }}" required
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Jenis Usaha *</label>
                        <input type="text" name="business_type" value="{{ old('business_type', $store->business_type) }}" required
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('business_type') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Lengkap Toko</label>
                    <textarea name="address" rows="2" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('address', $store->address) }}</textarea>
                    @error('address') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Logo Toko (Opsional, maks. 2MB)</label>
                    <div class="flex items-center gap-4">
                        @if($store->logo_path)
                            <img src="{{ asset('storage/' . $store->logo_path) }}" alt="{{ $store->name }}" class="w-12 h-12 rounded-xl object-cover border border-slate-200">
                        @endif
                        <input type="file" name="logo" accept="image/*" class="text-xs text-slate-600">
                    </div>
                    @error('logo') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </section>

            {{-- Loyalty Settings --}}
            <section class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-sm shadow-indigo-100/50 space-y-4">
                <h2 class="text-base font-bold text-slate-900">Aturan Poin Loyalitas</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nilai Tukar 1 Poin (Rp) *</label>
                        <input type="number" name="point_value_idr" value="{{ old('point_value_idr', $store->point_value_idr) }}" min="1" required
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="text-[11px] text-slate-500 mt-1">Misal: 100 berarti 100 poin bernilai potongan Rp 10.000.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Minimal Poin Member VIP *</label>
                        <input type="number" name="vip_points_threshold" value="{{ old('vip_points_threshold', $store->vip_points_threshold) }}" min="10" required
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <p class="text-[11px] text-slate-500 mt-1">Digunakan untuk badge VIP &amp; segmentasi broadcast email VIP.</p>
                    </div>
                </div>
            </section>

            {{-- Email & SMTP Settings --}}
            <section class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-sm shadow-indigo-100/50 space-y-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Konfigurasi Pengirim Email &amp; SMTP</h2>
                    <p class="text-xs text-slate-500">Kosongkan kolom SMTP jika ingin menggunakan mailer default sistem (.env / Mailtrap).</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pengirim (Sender Name)</label>
                        <input type="text" name="sender_name" value="{{ old('sender_name', $store->sender_name) }}" placeholder="{{ $store->name }}"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email Pengirim (Sender Email)</label>
                        <input type="email" name="sender_email" value="{{ old('sender_email', $store->sender_email) }}" placeholder="halo@tokoanda.com"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">SMTP Host</label>
                        <input type="text" name="smtp_host" value="{{ old('smtp_host', $store->smtp_host) }}" placeholder="smtp-relay.brevo.com"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">SMTP Port</label>
                        <input type="number" name="smtp_port" value="{{ old('smtp_port', $store->smtp_port) }}" placeholder="587"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Enkripsi</label>
                        <select name="smtp_encryption" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Tidak ada</option>
                            <option value="tls" @selected(old('smtp_encryption', $store->smtp_encryption) === 'tls')>TLS</option>
                            <option value="ssl" @selected(old('smtp_encryption', $store->smtp_encryption) === 'ssl')>SSL</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">SMTP Username</label>
                        <input type="text" name="smtp_username" value="{{ old('smtp_username', $store->smtp_username) }}"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">SMTP Password</label>
                        <input type="password" name="smtp_password" placeholder="{{ $store->smtp_password ? '•••••••• (tersimpan, isi untuk ganti)' : 'Password / API Key SMTP' }}"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-sm hover:bg-indigo-700 active:scale-[0.99] transition">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Simpan Pengaturan Toko
                </button>
            </div>
        </form>

        {{-- Test Email Form --}}
        <section class="rounded-2xl bg-white border border-slate-200/80 p-5 sm:p-6 shadow-sm shadow-indigo-100/50">
            <h2 class="text-sm font-bold text-slate-900 mb-1">Uji Pengiriman Email</h2>
            <p class="text-xs text-slate-500 mb-3">Kirim email uji coba untuk memastikan konfigurasi pengirim bekerja dengan baik.</p>
            <form method="POST" action="{{ route('settings.test-email') }}" class="flex flex-col sm:flex-row gap-3">
                @csrf
                <input type="email" name="test_email" value="{{ auth()->user()->email }}" required placeholder="Masukkan alamat email tujuan"
                       class="flex-1 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-bold hover:bg-indigo-100 transition">
                    Kirim Email Uji Coba
                </button>
            </form>
        </section>
    </div>
</x-app-layout>
