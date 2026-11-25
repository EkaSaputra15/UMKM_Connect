<x-app-layout>
    <div class="space-y-6" x-data="{ editingService: null }">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Katalog Layanan Toko</h1>
            <p class="text-xs text-slate-600 mt-1">Atur daftar layanan, harga jual, dan poin loyalitas yang diberikan kepada pelanggan.</p>
        </div>

        {{-- Add New Service --}}
        <section class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm shadow-indigo-100/50">
            <h2 class="text-sm font-bold text-slate-900 mb-3">Tambah Layanan Baru</h2>
            <form method="POST" action="{{ route('services.store') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end">
                @csrf
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Layanan *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Deep Clean Sepatu"
                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Harga (Rp) *</label>
                    <input type="number" name="price" value="{{ old('price') }}" min="0" step="500" required placeholder="50000"
                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('price') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reward Poin *</label>
                    <div class="flex gap-2">
                        <input type="number" name="points_reward" value="{{ old('points_reward', 10) }}" min="0" required
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 shrink-0">
                            Tambah
                        </button>
                    </div>
                    @error('points_reward') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </form>
        </section>

        {{-- Services Table --}}
        <section class="rounded-2xl bg-white border border-slate-200/80 shadow-sm shadow-indigo-100/50 overflow-hidden">
            @if($services->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3">Nama Layanan</th>
                                <th class="px-5 py-3 text-right">Harga</th>
                                <th class="px-5 py-3 text-center">Reward Poin</th>
                                <th class="px-5 py-3 text-center">Status</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($services as $service)
                                <tr class="hover:bg-indigo-50/50 transition">
                                    <td class="px-5 py-3.5 font-bold text-slate-900">{{ $service->name }}</td>
                                    <td class="px-5 py-3.5 text-right font-semibold text-slate-800">
                                        Rp {{ number_format((float)$service->price, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold">
                                            +{{ $service->points_reward }} poin
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $service->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $service->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <button type="button"
                                                    @click="editingService = {{ Js::from([
                                                        'id' => $service->id,
                                                        'name' => $service->name,
                                                        'price' => (float) $service->price,
                                                        'points_reward' => $service->points_reward,
                                                        'is_active' => $service->is_active,
                                                        'url' => route('services.update', $service),
                                                    ]) }}"
                                                    class="px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-indigo-50 hover:text-indigo-600 transition">
                                                Edit
                                            </button>
                                            <form method="POST" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Hapus layanan {{ $service->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-600 text-xs font-semibold hover:bg-rose-100 transition">
                                                    Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-slate-100">{{ $services->links() }}</div>
            @else
                <div class="px-5 py-10 text-center text-sm text-slate-500">Belum ada layanan yang ditambahkan.</div>
            @endif
        </section>

        {{-- Edit Modal --}}
        <div x-show="editingService !== null" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white border border-slate-200 shadow-xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-900">Edit Layanan</h2>
                    <button type="button" @click="editingService = null" class="p-1.5 rounded-xl text-slate-400 hover:bg-slate-100">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form method="POST" :action="editingService?.url" class="space-y-3.5">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Layanan *</label>
                        <input type="text" name="name" x-model="editingService.name" required
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Harga (Rp) *</label>
                            <input type="number" name="price" x-model="editingService.price" min="0" required
                                   class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Reward Poin *</label>
                            <input type="number" name="points_reward" x-model="editingService.points_reward" min="0" required
                                   class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer pt-1">
                        <input type="checkbox" name="is_active" value="1" x-model="editingService.is_active"
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-xs font-semibold text-slate-700">Layanan Aktif (Tampil di Kasir)</span>
                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editingService = null" class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
