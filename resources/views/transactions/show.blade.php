<x-app-layout>
    <div class="space-y-6 max-w-2xl mx-auto">
        <div class="no-print flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('transactions.index') }}" class="p-2 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-indigo-50 hover:text-indigo-600 transition">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <div>
                    <h1 class="text-lg font-extrabold text-slate-900">Detail &amp; Nota Digital</h1>
                    <p class="text-xs text-slate-500">{{ $transaction->invoice_code }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @if($transaction->customer?->email)
                    <form method="POST" action="{{ route('transactions.resend-receipt', $transaction) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-indigo-50 text-indigo-700 text-xs font-semibold hover:bg-indigo-100 transition">
                            <i data-lucide="mail" class="w-3.5 h-3.5"></i>
                            Kirim Ulang Nota Email
                        </button>
                    </form>
                @endif
                <button type="button" onclick="window.print()"
                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 text-white text-xs font-semibold hover:bg-indigo-700 transition">
                    <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                    Cetak Nota
                </button>
            </div>
        </div>

        {{-- Receipt Card --}}
        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm shadow-indigo-100/50 overflow-hidden">
            <div class="bg-indigo-600 text-white p-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="text-xs uppercase tracking-wider text-indigo-100 font-semibold">Nota Transaksi Resmi</div>
                        <h2 class="text-xl font-extrabold mt-1">{{ $transaction->store?->name }}</h2>
                        @if($transaction->store?->address)
                            <p class="text-xs text-indigo-100 mt-1">{{ $transaction->store->address }}</p>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-indigo-100">Kode Nota</div>
                        <div class="text-sm font-bold">{{ $transaction->invoice_code }}</div>
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-2 gap-4 rounded-xl bg-slate-50 p-4 text-xs">
                    <div>
                        <div class="text-slate-500">Pelanggan</div>
                        <div class="font-bold text-slate-900 mt-0.5">{{ $transaction->customer?->name }}</div>
                        <div class="text-slate-600">{{ $transaction->customer?->whatsapp_number }}</div>
                        @if($transaction->customer?->email)
                            <div class="text-slate-500">{{ $transaction->customer->email }}</div>
                        @endif
                    </div>
                    <div class="text-right">
                        <div class="text-slate-500">Tanggal Transaksi</div>
                        <div class="font-bold text-slate-900 mt-0.5">{{ $transaction->created_at?->format('d M Y, H:i') }}</div>
                        <div class="text-slate-600 mt-1">Kasir: {{ $transaction->user?->name ?? '-' }}</div>
                    </div>
                </div>

                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs font-semibold uppercase text-slate-500">
                            <th class="py-2.5">Layanan</th>
                            <th class="py-2.5 text-center">Qty</th>
                            <th class="py-2.5 text-right">Harga</th>
                            <th class="py-2.5 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($transaction->service_detail ?? [] as $item)
                            <tr>
                                <td class="py-3 font-medium text-slate-900">{{ $item['name'] ?? '-' }}</td>
                                <td class="py-3 text-center text-slate-600">{{ $item['qty'] ?? 1 }}</td>
                                <td class="py-3 text-right text-slate-600">Rp {{ number_format((float)($item['price'] ?? 0), 0, ',', '.') }}</td>
                                <td class="py-3 text-right font-semibold text-slate-900">Rp {{ number_format((float)($item['subtotal'] ?? 0), 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="border-t border-slate-200 pt-4 space-y-2 text-sm">
                    <div class="flex justify-between text-slate-600">
                        <span>Subtotal</span>
                        <span>Rp {{ number_format((float)$transaction->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($transaction->points_used > 0)
                        <div class="flex justify-between text-emerald-600 font-medium">
                            <span>Diskon Tukar Poin ({{ number_format($transaction->points_used) }} poin)</span>
                            <span>- Rp {{ number_format((float)$transaction->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-lg font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                        <span>Total Bayar</span>
                        <span class="text-indigo-600">Rp {{ number_format((float)$transaction->total_paid, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="rounded-xl bg-indigo-50 border border-indigo-100 p-4 flex flex-wrap items-center justify-between gap-2 text-xs">
                    <div>
                        <span class="font-semibold text-indigo-900">Poin Didapat:</span>
                        <span class="font-bold text-indigo-700">+{{ number_format($transaction->points_earned) }} poin</span>
                    </div>
                    <div>
                        <span class="font-semibold text-indigo-900">Saldo Poin Pelanggan Saat Ini:</span>
                        <span class="font-bold text-indigo-700">{{ number_format($transaction->customer?->total_points ?? 0) }} poin</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
