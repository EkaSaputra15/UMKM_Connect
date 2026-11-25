<x-app-layout>
    <div class="space-y-6">
        {{-- Hero Welcome Banner (PRD Section 5) --}}
        <section class="rounded-2xl bg-gradient-to-r from-indigo-600 via-indigo-600 to-indigo-700 text-white p-6 shadow-sm shadow-indigo-100/50 relative overflow-hidden">
            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-white/15 text-indigo-50 text-xs font-semibold mb-2">
                        <i data-lucide="sparkles" class="w-3.5 h-3.5"></i>
                        {{ auth()->user()->store?->business_type ?? 'UMKM Layanan' }}
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight">
                        Selamat Datang, {{ auth()->user()->name }}!
                    </h1>
                    <p class="text-sm text-indigo-100 mt-1">
                        Pantau aktivitas pelanggan, poin loyalitas, dan transaksi harian <strong>{{ auth()->user()->store?->name }}</strong> secara real-time.
                    </p>
                </div>

                {{-- Quick Actions (PRD Section 3.2) --}}
                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <a href="{{ route('transactions.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white text-indigo-700 text-sm font-bold shadow-sm hover:bg-indigo-50 active:scale-[0.98] transition">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        + Transaksi Baru
                    </a>
                    <a href="{{ route('customers.index', ['create' => 1]) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-500/40 border border-white/25 text-white text-sm font-semibold hover:bg-indigo-500/60 active:scale-[0.98] transition">
                        <i data-lucide="user-plus" class="w-4 h-4"></i>
                        + Pelanggan Baru
                    </a>
                </div>
            </div>
        </section>

        {{-- Quick Stats Cards (PRD Section 3.2) --}}
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm shadow-indigo-100/50">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Pelanggan Aktif</span>
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i data-lucide="users" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 text-2xl font-extrabold text-slate-900">{{ number_format($activeCustomersCount) }}</div>
                <div class="mt-1 text-xs text-slate-500">
                    Dari total <span class="font-semibold text-slate-700">{{ number_format($totalCustomersCount) }}</span> member terdaftar
                </div>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm shadow-indigo-100/50">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Transaksi Hari Ini</span>
                    <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 text-2xl font-extrabold text-slate-900">{{ number_format($todayTransactionsCount) }}</div>
                <div class="mt-1 text-xs text-slate-500">Nota tercatat hari ini</div>
            </div>

            <div class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm shadow-indigo-100/50">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Omzet Hari Ini</span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i data-lucide="wallet" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 text-2xl font-extrabold text-slate-900">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</div>
                <div class="mt-1 text-xs text-slate-500">Total pembayaran bersih</div>
            </div>
        </section>

        {{-- Weekly Visit Trend Chart --}}
        <section class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm shadow-indigo-100/50">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Grafik Kunjungan Mingguan</h2>
                    <p class="text-xs text-slate-500">Tren jumlah kunjungan pelanggan dalam 7 hari terakhir</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold w-fit">
                    <i data-lucide="trending-up" class="w-3.5 h-3.5"></i>
                    7 Hari Terakhir
                </span>
            </div>
            <div class="h-64">
                <canvas id="weeklyVisitsChart"></canvas>
            </div>
        </section>

        {{-- Live Feed Transaksi (5 Transaksi Terakhir) --}}
        <section class="rounded-2xl bg-white border border-slate-200/80 shadow-sm shadow-indigo-100/50 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Live Feed Transaksi</h2>
                    <p class="text-xs text-slate-500">5 transaksi terbaru yang masuk di kasir</p>
                </div>
                <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 inline-flex items-center gap-1">
                    Lihat Semua
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            @if($recentTransactions->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                <th class="px-5 py-3">Kode Nota</th>
                                <th class="px-5 py-3">Pelanggan</th>
                                <th class="px-5 py-3">Kasir</th>
                                <th class="px-5 py-3 text-right">Total Bayar</th>
                                <th class="px-5 py-3 text-center">Poin</th>
                                <th class="px-5 py-3 text-right">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">
                            @foreach($recentTransactions as $tx)
                                <tr class="hover:bg-indigo-50/50 transition cursor-pointer" onclick="window.location='{{ route('transactions.show', $tx) }}'">
                                    <td class="px-5 py-3.5 font-semibold text-indigo-600 whitespace-nowrap">
                                        {{ $tx->invoice_code }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="font-semibold text-slate-900">{{ $tx->customer?->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">{{ $tx->customer?->whatsapp_number }}</div>
                                    </td>
                                    <td class="px-5 py-3.5 text-slate-600">{{ $tx->user?->name ?? '-' }}</td>
                                    <td class="px-5 py-3.5 text-right font-bold text-slate-900 whitespace-nowrap">
                                        Rp {{ number_format((float)$tx->total_paid, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-center whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700">
                                            +{{ $tx->points_earned }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-right text-xs text-slate-500 whitespace-nowrap">
                                        {{ $tx->created_at?->format('d M, H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-5 py-10 text-center text-sm text-slate-500">
                    Belum ada transaksi yang tercatat. Mulai transaksi pertama Anda melalui tombol <strong>+ Transaksi Baru</strong>.
                </div>
            @endif
        </section>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const canvas = document.getElementById('weeklyVisitsChart');
                if (!canvas || typeof window.Chart === 'undefined') return;

                new window.Chart(canvas, {
                    type: 'bar',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [
                            {
                                label: 'Jumlah Kunjungan',
                                data: @json($chartVisits),
                                backgroundColor: 'rgba(79, 70, 229, 0.85)',
                                hoverBackgroundColor: '#4338ca',
                                borderRadius: 8,
                                barThickness: 28,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                padding: 10,
                                cornerRadius: 10,
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { precision: 0, color: '#64748b' },
                                grid: { color: '#f1f5f9' }
                            },
                            x: {
                                ticks: { color: '#64748b' },
                                grid: { display: false }
                            }
                        }
                    }
                });
            });
        </script>
    @endpush
</x-app-layout>
