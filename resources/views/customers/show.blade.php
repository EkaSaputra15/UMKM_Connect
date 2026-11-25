<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('customers.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-xl font-extrabold text-slate-900">Profil Pelanggan</h1>
                    <p class="text-xs text-slate-500">Detail akumulasi poin, status tier loyalitas, dan riwayat transaksi</p>
                </div>
            </div>
            <a href="{{ route('transactions.create', ['customer_id' => $customer->id]) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-sm shadow-indigo-100/50 hover:bg-indigo-700 transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Transaksi untuk {{ $customer->name }}
            </a>
        </div>

        {{-- Customer Summary Card --}}
        <section class="rounded-2xl bg-white border border-slate-200/80 p-6 shadow-sm shadow-indigo-100/50">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-5">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white font-extrabold text-xl flex items-center justify-center shrink-0">
                        {{ strtoupper(mb_substr($customer->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-lg font-extrabold text-slate-900">{{ $customer->name }}</h2>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $customer->loyalty_tier === 'VIP' ? 'bg-amber-100 text-amber-800' : 'bg-indigo-50 text-indigo-700' }}">
                                Tier {{ $customer->loyalty_tier }}
                            </span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-600">
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="phone" class="w-3.5 h-3.5 text-slate-400"></i>
                                {{ $customer->whatsapp_number }}
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                                {{ $customer->email ?: 'Belum ada email' }}
                            </span>
                            @if($customer->birth_date)
                                <span class="inline-flex items-center gap-1.5">
                                    <i data-lucide="cake" class="w-3.5 h-3.5 text-slate-400"></i>
                                    {{ $customer->birth_date->format('d M Y') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3 sm:gap-4 shrink-0">
                    <div class="rounded-xl bg-indigo-50/70 border border-indigo-100 px-4 py-3 text-center">
                        <div class="text-[11px] font-semibold text-indigo-600 uppercase">Saldo Poin</div>
                        <div class="text-lg font-extrabold text-indigo-700 mt-0.5">{{ number_format($customer->total_points) }}</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200/80 px-4 py-3 text-center">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase">Kunjungan</div>
                        <div class="text-lg font-extrabold text-slate-900 mt-0.5">{{ number_format($visitCount) }}x</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 border border-slate-200/80 px-4 py-3 text-center">
                        <div class="text-[11px] font-semibold text-slate-500 uppercase">Total Belanja</div>
                        <div class="text-sm font-extrabold text-slate-900 mt-1">Rp {{ number_format($totalSpent, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100 text-xs text-slate-500">
                Kunjungan terakhir:
                <span class="font-semibold text-slate-700">
                    {{ $customer->last_visit_at ? $customer->last_visit_at->format('d M Y, H:i') : 'Belum pernah berkunjung' }}
                </span>
            </div>
        </section>

        {{-- Transaction History --}}
        <section class="rounded-2xl bg-white border border-slate-200/80 shadow-sm shadow-indigo-100/50 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Riwayat Transaksi Lengkap</h3>
            </div>

            @if($transactions->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3">Kode Nota</th>
                                <th class="px-5 py-3">Layanan</th>
                                <th class="px-5 py-3">Kasir</th>
                                <th class="px-5 py-3 text-right">Total Bayar</th>
                                <th class="px-5 py-3 text-center">Poin (+/-)</th>
                                <th class="px-5 py-3 text-right">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($transactions as $tx)
                                <tr class="hover:bg-indigo-50/50 transition">
                                    <td class="px-5 py-3.5 font-semibold text-indigo-600 whitespace-nowrap">
                                        <a href="{{ route('transactions.show', $tx) }}" class="hover:underline">{{ $tx->invoice_code }}</a>
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-700">
                                        @foreach($tx->service_detail ?? [] as $item)
                                            <div>{{ $item['name'] ?? '-' }} ({{ $item['qty'] ?? 1 }}x)</div>
                                        @endforeach
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600">{{ $tx->user?->name ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-right font-bold text-slate-900 whitespace-nowrap">
                                        Rp {{ number_format((float)$tx->total_paid, 0, ',', '.') }}
                                        @if($tx->discount_amount > 0)
                                            <div class="text-[11px] font-normal text-emerald-600">Diskon Rp {{ number_format((float)$tx->discount_amount, 0, ',', '.') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap text-xs">
                                        @if($tx->points_used > 0)
                                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-semibold">-{{ $tx->points_used }}</span>
                                        @endif
                                        <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-semibold">+{{ $tx->points_earned }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-xs text-slate-500 whitespace-nowrap">
                                        {{ $tx->created_at?->format('d M Y, H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-5 py-4 border-t border-slate-100">{{ $transactions->links() }}</div>
            @else
                <div class="px-5 py-10 text-center text-sm text-slate-500">Pelanggan ini belum memiliki transaksi.</div>
            @endif
        </section>
    </div>
</x-app-layout>
