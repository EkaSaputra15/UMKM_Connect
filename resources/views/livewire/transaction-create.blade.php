<div class="space-y-6">
    <div>
        <h1 class="text-xl font-extrabold text-slate-900">Kasir &amp; Transaksi Baru</h1>
        <p class="text-xs text-slate-600 mt-1">Alur cepat kasir (&lt; 30 detik): Pilih/Daftarkan Pelanggan &rarr; Pilih Layanan &rarr; Tukar Poin &amp; Checkout.</p>
    </div>

    @if($completedTransactionId)
        {{-- Checkout Success Screen --}}
        <div class="rounded-2xl bg-white border border-emerald-200 p-6 sm:p-8 shadow-sm shadow-indigo-100/50 text-center max-w-xl mx-auto">
            <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="check-circle-2" class="w-8 h-8"></i>
            </div>
            <h2 class="text-xl font-extrabold text-slate-900">Transaksi Berhasil Disimpan!</h2>
            <p class="text-sm text-slate-600 mt-1">Kode Nota: <span class="font-bold text-indigo-600">{{ $completedInvoiceCode }}</span></p>

            <div class="mt-4 rounded-xl px-4 py-3 text-xs font-medium {{ $completedReceiptQueued ? 'bg-indigo-50 text-indigo-700' : 'bg-amber-50 text-amber-800' }}">
                @if($completedReceiptQueued)
                    Nota digital otomatis sedang dikirim ke email pelanggan melalui antrean latar belakang.
                @else
                    Transaksi tersimpan. Pelanggan tidak memiliki email sehingga nota digital email tidak dikirimkan.
                @endif
            </div>

            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <a href="{{ route('transactions.show', $completedTransactionId) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-600 transition">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    Lihat / Cetak Nota
                </a>
                <button type="button" wire:click="startNewTransaction"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-sm hover:bg-indigo-700 active:scale-[0.98] transition">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    + Transaksi Baru
                </button>
            </div>
        </div>
    @else
        @error('checkout')
            <div class="rounded-2xl bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800 flex items-center gap-2.5">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 items-start">
            {{-- Left Area: Step 1 (Customer) & Step 2 (Services) --}}
            <div class="xl:col-span-7 space-y-5">
                {{-- Step 1: Customer-Centric Selection --}}
                <section class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm shadow-indigo-100/50">
                    <div class="flex items-center justify-between gap-3 mb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">1</span>
                            <h2 class="text-sm font-bold text-slate-900">Pilih atau Daftarkan Pelanggan</h2>
                        </div>
                        @if(! $this->selectedCustomer)
                            <button type="button" wire:click="toggleQuickCustomerForm"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-semibold hover:bg-indigo-100 transition">
                                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                                {{ $showQuickCustomerForm ? 'Tutup Form' : '+ Pelanggan Baru' }}
                            </button>
                        @endif
                    </div>

                    @if($this->selectedCustomer)
                        @php $c = $this->selectedCustomer; @endphp
                        <div class="rounded-xl bg-indigo-50/70 border border-indigo-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900">{{ $c->name }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-white text-indigo-700 border border-indigo-200">
                                        {{ $c->loyalty_tier }}
                                    </span>
                                </div>
                                <div class="text-xs text-slate-600 mt-1">
                                    {{ $c->whatsapp_number }}
                                    @if($c->email) &bull; {{ $c->email }} @else &bull; <span class="text-amber-700">Tanpa email (nota tidak dikirim)</span> @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <div class="text-[11px] font-semibold text-indigo-600 uppercase">Saldo Poin</div>
                                    <div class="text-base font-extrabold text-indigo-700">{{ number_format($c->total_points) }} poin</div>
                                </div>
                                <button type="button" wire:click="clearCustomer"
                                        class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-600 hover:text-rose-600 hover:border-rose-200 transition">
                                    Ganti
                                </button>
                            </div>
                        </div>
                    @elseif($showQuickCustomerForm)
                        <form wire:submit="createQuickCustomer" class="rounded-xl bg-slate-50 border border-slate-200 p-4 space-y-3">
                            <div class="text-xs font-bold text-slate-700">Registrasi Cepat Member Baru</div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <input type="text" wire:model="newCustomerName" placeholder="Nama lengkap *"
                                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('newCustomerName') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <input type="text" wire:model="newCustomerWhatsapp" placeholder="No. WhatsApp (08...) *"
                                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('newCustomerWhatsapp') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <input type="email" wire:model="newCustomerEmail" placeholder="Email (untuk nota digital)"
                                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('newCustomerEmail') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <input type="date" wire:model="newCustomerBirthDate" title="Tanggal Lahir (Opsional)"
                                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @error('newCustomerBirthDate') <p class="text-[11px] text-rose-600 mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div class="flex justify-end gap-2">
                                <button type="button" wire:click="toggleQuickCustomerForm" class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-600">
                                    Batal
                                </button>
                                <button type="submit" class="px-4 py-1.5 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700">
                                    Simpan &amp; Pilih Pelanggan
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="space-y-3">
                            <div class="relative">
                                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                                <input type="text"
                                       wire:model.live.debounce.150ms="customerSearch"
                                       placeholder="Cari cepat berdasarkan nama atau nomor WhatsApp..."
                                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            </div>

                            @if($customerResults->isNotEmpty())
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach($customerResults as $cust)
                                        <button type="button"
                                                wire:key="pick-cust-{{ $cust->id }}"
                                                wire:click="selectCustomer({{ $cust->id }})"
                                                class="text-left p-3 rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/50 transition flex items-center justify-between gap-2">
                                            <div class="min-w-0">
                                                <div class="text-sm font-semibold text-slate-900 truncate">{{ $cust->name }}</div>
                                                <div class="text-xs text-slate-500 truncate">{{ $cust->whatsapp_number }}</div>
                                            </div>
                                            <span class="shrink-0 px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-[11px] font-bold">
                                                {{ number_format($cust->total_points) }} pt
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-xs text-slate-500 py-3 text-center">
                                    Pelanggan tidak ditemukan. Klik <strong>+ Pelanggan Baru</strong> untuk registrasi cepat.
                                </div>
                            @endif
                        </div>
                    @endif
                </section>

                {{-- Step 2: Service Catalog --}}
                <section class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm shadow-indigo-100/50 {{ ! $this->selectedCustomer ? 'opacity-60' : '' }}">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">2</span>
                            <h2 class="text-sm font-bold text-slate-900">Pilih Layanan Toko</h2>
                        </div>
                        <input type="text"
                               wire:model.live.debounce.150ms="serviceSearch"
                               placeholder="Filter layanan..."
                               class="sm:w-56 px-3 py-1.5 rounded-xl border-slate-200 text-xs focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    @if($services->isNotEmpty())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($services as $service)
                                @php $inCartQty = $cart[$service->id]['qty'] ?? 0; @endphp
                                <button type="button"
                                        wire:key="srv-{{ $service->id }}"
                                        wire:click="addService({{ $service->id }})"
                                        @disabled(! $this->selectedCustomer)
                                        class="text-left p-4 rounded-xl border transition flex flex-col justify-between gap-2 {{ $inCartQty > 0 ? 'border-indigo-500 bg-indigo-50/50 ring-1 ring-indigo-500' : 'border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/40' }} disabled:cursor-not-allowed">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="font-bold text-sm text-slate-900">{{ $service->name }}</div>
                                        @if($inCartQty > 0)
                                            <span class="px-2 py-0.5 rounded-full bg-indigo-600 text-white text-[11px] font-bold">x{{ $inCartQty }}</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center justify-between pt-1">
                                        <span class="text-sm font-extrabold text-indigo-600">Rp {{ number_format((float)$service->price, 0, ',', '.') }}</span>
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700">+{{ $service->points_reward }} poin</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div class="text-xs text-slate-500 py-6 text-center">
                            Belum ada layanan aktif tersedia. Hubungi Owner untuk menambahkan katalog layanan.
                        </div>
                    @endif
                </section>
            </div>

            {{-- Right Area: Step 3 (Cart, Loyalty Point Decision Engine & Checkout) --}}
            <div class="xl:col-span-5">
                <section class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm shadow-indigo-100/50 space-y-5 sticky top-6">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-bold flex items-center justify-center">3</span>
                        <h2 class="text-sm font-bold text-slate-900">Ringkasan &amp; Penukaran Poin</h2>
                    </div>

                    {{-- Cart Items --}}
                    @if(! empty($cart))
                        <div class="divide-y divide-slate-100 border border-slate-100 rounded-xl overflow-hidden">
                            @foreach($cart as $id => $item)
                                <div wire:key="cart-item-{{ $id }}" class="p-3 bg-slate-50/50 flex items-center justify-between gap-2">
                                    <div class="min-w-0 flex-1">
                                        <div class="text-xs font-bold text-slate-900 truncate">{{ $item['name'] }}</div>
                                        <div class="text-[11px] text-slate-500">
                                            Rp {{ number_format($item['price'], 0, ',', '.') }} &bull; +{{ $item['points_reward'] * $item['qty'] }} poin
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <button type="button" wire:click="decrementQty({{ $id }})"
                                                class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100">-</button>
                                        <span class="w-6 text-center text-xs font-bold text-slate-900">{{ $item['qty'] }}</span>
                                        <button type="button" wire:click="incrementQty({{ $id }})"
                                                class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold text-xs hover:bg-slate-100">+</button>
                                        <button type="button" wire:click="removeService({{ $id }})"
                                                class="ml-1 p-1.5 rounded-lg text-rose-500 hover:bg-rose-50">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-200 py-8 text-center text-xs text-slate-500">
                            Keranjang layanan masih kosong. Pilih pelanggan lalu klik layanan di sebelah kiri.
                        </div>
                    @endif

                    {{-- Decision Engine Penukaran Poin (PRD Section 3.4) --}}
                    @if($this->selectedCustomer)
                        <div class="rounded-xl bg-indigo-50/60 border border-indigo-100 p-4 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                                <div>
                                    <div class="text-xs font-bold text-indigo-900">Decision Engine Poin Loyalitas</div>
                                    <div class="text-[11px] text-indigo-700">
                                        Poin tersedia: <strong>{{ number_format($this->selectedCustomer->total_points) }} poin</strong>
                                        (1 poin = Rp {{ number_format($this->pointValueIdr, 0, ',', '.') }})
                                    </div>
                                </div>
                            </div>

                            @if($this->maxRedeemablePoints > 0)
                                <label class="flex items-center justify-between gap-3 cursor-pointer bg-white rounded-xl border border-indigo-200 px-3 py-2.5">
                                    <span class="text-xs font-semibold text-slate-800">Tukar poin menjadi diskon?</span>
                                    <input type="checkbox" wire:model.live="redeemPoints"
                                           class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </label>

                                @if($redeemPoints)
                                    <div>
                                        <div class="flex items-center justify-between text-[11px] text-slate-600 mb-1">
                                            <span>Jumlah poin ditukar (Maks. {{ number_format($this->maxRedeemablePoints) }})</span>
                                            <span class="font-bold text-emerald-600">- Rp {{ number_format($this->discountAmount, 0, ',', '.') }}</span>
                                        </div>
                                        <input type="number"
                                               min="0"
                                               max="{{ $this->maxRedeemablePoints }}"
                                               wire:model.live="pointsToUse"
                                               class="w-full rounded-xl border-indigo-200 text-sm font-bold text-indigo-700 focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                @endif
                            @else
                                <div class="text-[11px] text-slate-500">
                                    {{ $this->selectedCustomer->total_points <= 0 ? 'Pelanggan belum memiliki saldo poin untuk ditukar.' : 'Tambahkan layanan terlebih dahulu untuk menukar poin.' }}
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Totals --}}
                    <div class="space-y-2 text-sm border-t border-slate-100 pt-4">
                        <div class="flex justify-between text-slate-600">
                            <span>Subtotal Layanan</span>
                            <span class="font-semibold text-slate-900">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-600">
                            <span>Diskon Tukar Poin ({{ number_format($this->effectivePointsUsed) }} pt)</span>
                            <span class="font-semibold">- Rp {{ number_format($this->discountAmount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-base font-extrabold text-slate-900 pt-2 border-t border-slate-100">
                            <span>Total Bayar</span>
                            <span class="text-indigo-600">Rp {{ number_format($this->totalPaid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-xs text-indigo-700 bg-indigo-50 rounded-xl px-3 py-2 font-semibold">
                            <span>Estimasi Poin Didapat</span>
                            <span>+{{ number_format($this->pointsEarned) }} Poin</span>
                        </div>
                    </div>

                    <button type="button"
                            wire:click="checkout"
                            wire:loading.attr="disabled"
                            @disabled(! $this->selectedCustomer || empty($cart))
                            class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-indigo-600 text-white text-sm font-bold shadow-sm shadow-indigo-100/50 hover:bg-indigo-700 active:scale-[0.99] transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="check-circle" class="w-4 h-4"></i>
                        <span wire:loading.remove wire:target="checkout">Simpan Transaksi &amp; Kirim Nota</span>
                        <span wire:loading wire:target="checkout">Memproses Transaksi...</span>
                    </button>
                </section>
            </div>
        </div>
    @endif
</div>
