<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-extrabold text-slate-900">Manajemen Pelanggan (CRM)</h1>
            <p class="text-xs text-slate-600 mt-1">Cari pelanggan secara instan, pantau saldo poin loyalitas, atau daftarkan member baru.</p>
        </div>
        <button wire:click="openCreateModal" type="button"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-sm shadow-indigo-100/50 hover:bg-indigo-700 active:scale-[0.99] transition">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            + Pelanggan Baru
        </button>
    </div>

    {{-- Search & Segment Filter Bar --}}
    <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-sm shadow-indigo-100/50 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center gap-3">
            <div class="relative flex-1">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text"
                       wire:model.live.debounce.150ms="search"
                       placeholder="Ketik nama, nomor WhatsApp, atau email pelanggan..."
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div class="flex flex-wrap items-center gap-1.5">
                @foreach([
                    'all' => 'Semua',
                    'active' => 'Aktif (≤30 Hari)',
                    'inactive_30_days' => 'Pasif (>30 Hari)',
                    'vip' => 'VIP (≥' . number_format($vipThreshold) . ' Poin)',
                ] as $key => $label)
                    <button type="button"
                            wire:click="$set('segment', '{{ $key }}')"
                            class="px-3 py-2 rounded-xl text-xs font-semibold transition {{ $segment === $key ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Customers Table --}}
    <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm shadow-indigo-100/50 overflow-hidden">
        @if($customers->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                            <th class="px-5 py-3">Pelanggan</th>
                            <th class="px-5 py-3">Kontak (WA &amp; Email)</th>
                            <th class="px-5 py-3">Tier &amp; Poin</th>
                            <th class="px-5 py-3">Kunjungan Terakhir</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($customers as $customer)
                            @php
                                $tier = $customer->loyalty_tier;
                                $tierClass = match ($tier) {
                                    'VIP' => 'bg-amber-100 text-amber-800',
                                    'Silver' => 'bg-slate-200 text-slate-700',
                                    default => 'bg-indigo-50 text-indigo-700',
                                };
                                $isSelected = $selectedCustomerId === $customer->id;
                            @endphp
                            <tr wire:key="customer-{{ $customer->id }}"
                                wire:click="selectRow({{ $customer->id }})"
                                class="transition cursor-pointer {{ $isSelected ? 'bg-indigo-50/50' : 'hover:bg-indigo-50/50' }}">
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('customers.show', $customer) }}" class="font-bold text-slate-900 hover:text-indigo-600">
                                        {{ $customer->name }}
                                    </a>
                                    <div class="text-xs text-slate-500 mt-0.5">
                                        {{ $customer->transactions_count }} transaksi
                                        @if($customer->birth_date)
                                            &bull; Lahir {{ $customer->birth_date->format('d M') }}
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="text-slate-800 font-medium">{{ $customer->whatsapp_number }}</div>
                                    <div class="text-xs text-slate-500">{{ $customer->email ?: 'Belum ada email' }}</div>
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $tierClass }}">
                                            {{ $tier }}
                                        </span>
                                        <span class="font-bold text-indigo-600">{{ number_format($customer->total_points) }} poin</span>
                                    </div>
                                </td>
                                <td class="px-5 py-3.5 text-xs text-slate-600 whitespace-nowrap">
                                    {{ $customer->last_visit_at ? $customer->last_visit_at->format('d M Y, H:i') : 'Belum pernah transaksi' }}
                                </td>
                                <td class="px-5 py-3.5 text-right whitespace-nowrap" wire:click.stop>
                                    <div class="inline-flex items-center gap-1.5">
                                        <a href="{{ route('transactions.create', ['customer_id' => $customer->id]) }}"
                                           title="Buat Transaksi"
                                           class="px-2.5 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">
                                            Kasir
                                        </a>
                                        <a href="{{ route('customers.show', $customer) }}"
                                           class="px-2.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-indigo-50 hover:text-indigo-600 transition">
                                            Profil
                                        </a>
                                        <button type="button" wire:click="openEditModal({{ $customer->id }})"
                                                class="px-2.5 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-indigo-50 hover:text-indigo-600 transition">
                                            Edit
                                        </button>
                                        @if($customer->transactions_count === 0)
                                            <button type="button"
                                                    wire:click="deleteCustomer({{ $customer->id }})"
                                                    wire:confirm="Hapus data pelanggan {{ $customer->name }}?"
                                                    class="px-2.5 py-1.5 rounded-xl bg-rose-50 text-rose-600 text-xs font-semibold hover:bg-rose-100 transition">
                                                Hapus
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-4 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-900">Pelanggan tidak ditemukan</h3>
                <p class="text-xs text-slate-500 mt-1">Ubah kata kunci pencarian atau daftarkan pelanggan baru sekarang.</p>
            </div>
        @endif
    </div>

    {{-- Quick Registration / Edit Modal --}}
    @if($showFormModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
            <div class="w-full max-w-md rounded-2xl bg-white border border-slate-200 shadow-xl p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-bold text-slate-900">
                        {{ $editingCustomerId ? 'Edit Data Pelanggan' : 'Registrasi Cepat Pelanggan Baru' }}
                    </h2>
                    <button type="button" wire:click="closeModal" class="p-1.5 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-600">
                        <i data-lucide="x" class="w-4 h-4"></i>
                    </button>
                </div>

                <form wire:submit="saveCustomer" class="space-y-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" wire:model="name" placeholder="Contoh: Budi Santoso"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp *</label>
                        <input type="text" wire:model="whatsapp_number" placeholder="081234567890"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('whatsapp_number') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email (Untuk Nota Digital &amp; Promo)</label>
                        <input type="email" wire:model="email" placeholder="pelanggan@email.com"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('email') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Tanggal Lahir (Opsional)</label>
                        <input type="date" wire:model="birth_date"
                               class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('birth_date') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" wire:click="closeModal"
                                class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 shadow-sm">
                            {{ $editingCustomerId ? 'Simpan Perubahan' : 'Simpan Pelanggan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
