<x-app-layout>
    <div class="space-y-6">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Manajemen Staf &amp; Kasir</h1>
            <p class="text-xs text-slate-600 mt-1">Kelola akun Owner dan Kasir (Frontliner) yang memiliki akses ke toko Anda.</p>
        </div>

        {{-- Add Staff Form --}}
        <section class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm shadow-indigo-100/50">
            <h2 class="text-sm font-bold text-slate-900 mb-3">Tambah Akun Staf Baru</h2>
            <form method="POST" action="{{ route('staff.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Staf *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Rina Kasir"
                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email Login *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="rina@toko.com"
                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Peran *</label>
                    <select name="role" class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="cashier">Kasir / Frontliner</option>
                        <option value="owner">Owner (Akses Penuh)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kata Sandi *</label>
                    <input type="password" name="password" required placeholder="Min. 8 karakter"
                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('password') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Konfirmasi Sandi *</label>
                    <div class="flex gap-2">
                        <input type="password" name="password_confirmation" required placeholder="Ulangi sandi"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 shrink-0">
                            Simpan
                        </button>
                    </div>
                </div>
            </form>
        </section>

        {{-- Staff Table --}}
        <section class="rounded-2xl bg-white border border-slate-200/80 shadow-sm shadow-indigo-100/50 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Nama Staf</th>
                            <th class="px-5 py-3">Email</th>
                            <th class="px-5 py-3 text-center">Peran</th>
                            <th class="px-5 py-3 text-center">Transaksi</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($staff as $member)
                            <tr class="hover:bg-indigo-50/50 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-900">
                                    {{ $member->name }}
                                    @if($member->id === auth()->id())
                                        <span class="ml-1 text-[11px] font-medium text-indigo-600">(Anda)</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-slate-600">{{ $member->email }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $member->isOwner() ? 'bg-indigo-50 text-indigo-700' : 'bg-emerald-50 text-emerald-700' }}">
                                        {{ $member->isOwner() ? 'Owner' : 'Kasir' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center font-semibold text-slate-700">{{ $member->transactions_count }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $member->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                        {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                    @if($member->id !== auth()->id())
                                        <div class="inline-flex items-center gap-1.5">
                                            <form method="POST" action="{{ route('staff.toggle-status', $member) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-indigo-50 hover:text-indigo-600 transition">
                                                    {{ $member->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                            @if($member->transactions_count === 0)
                                                <form method="POST" action="{{ route('staff.destroy', $member) }}" onsubmit="return confirm('Hapus akun {{ $member->name }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 text-xs font-semibold hover:bg-rose-100 transition">
                                                        Hapus
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
