<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900">Riwayat Transaksi</h1>
                <p class="text-xs text-slate-600 mt-1">Daftar seluruh transaksi kasir, rincian poin loyalitas, dan cetak ulang nota digital.</p>
            </div>
            <a href="{{ route('transactions.create') }}"
               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-semibold shadow-sm hover:bg-indigo-700 transition">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                + Transaksi Baru
            </a>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('transactions.index') }}" class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-sm shadow-indigo-100/50">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="sm:col-span-2">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari kode nota, nama pelanggan, atau no. WA..."
                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex gap-2">
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="w-full rounded-xl border-slate-200 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <button type="submit" class="px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 shrink-0">
                        Filter
                    </button>
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="rounded-2xl bg-white border border-slate-200/80 shadow-sm shadow-indigo-100/50 overflow-hidden">
            @if($transactions->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3">Kode Nota</th>
                                <th class="px-5 py-3">Pelanggan</th>
                                <th class="px-5 py-3">Kasir</th>
                                <th class="px-5 py-3 text-right">Total Bayar</th>
                                <th class="px-5 py-3 text-center">Poin</th>
                                <th class="px-5 py-3 text-center">Nota Email</th>
                                <th class="px-5 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($transactions as $tx)
                                <tr class="hover:bg-indigo-50/50 transition">
                                    <td class="px-5 py-3.5 whitespace-nowrap">
                                        <a href="{{ route('transactions.show', $tx) }}" class="font-bold text-indigo-600 hover:underline">
                                            {{ $tx->invoice_code }}
                                        </a>
                                        <div class="text-[11px] text-slate-500">{{ $tx->created_at?->format('d M Y, H:i') }}</div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-slate-900">{{ $tx->customer?->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $tx->customer?->whatsapp_number }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600">{{ $tx->user?->name ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <div class="font-bold text-slate-900">Rp {{ number_format((float)$tx->total_paid, 0, ',', '.') }}</div>
                                        @if($tx->discount_amount > 0)
                                            <div class="text-[11px] text-emerald-600">Diskon Rp {{ number_format((float)$tx->discount_amount, 0, ',', '.') }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap text-xs">
                                        @if($tx->points_used > 0)
                                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-semibold">-{{ $tx->points_used }}</span>
                                        @endif
                                        <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-semibold">+{{ $tx->points_earned }}</span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        @if($tx->receipt_sent_at)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold">
                                                Terkirim
                                            </span>
                                        @elseif($tx->customer?->email)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 text-[11px] font-semibold">
                                                Antrean
                                            </span>
                                        @else
                                            <span class="text-xs text-slate-400">Tanpa Email</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <a href="{{ route('transactions.show', $tx) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-700 text-xs font-semibold hover:bg-indigo-50 hover:text-indigo-600 transition">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                            Nota
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-4 border-t border-slate-100">
                    {{ $transactions->links() }}
                </div>
            @else
                <div class="px-5 py-12 text-center text-sm text-slate-500">
                    Belum ada transaksi yang sesuai dengan filter.
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
