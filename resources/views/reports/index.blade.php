<x-app-layout>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-900">Laporan &amp; Analitik Bisnis</h1>
                <p class="text-xs text-slate-600 mt-1">Analisis tren pendapatan, pelanggan paling loyal (Top Spenders), dan performa layanan.</p>
            </div>

            <div class="inline-flex rounded-xl bg-white border border-slate-200 p-1 shadow-sm">
                @foreach(['daily' => 'Harian (14 Hari)', 'weekly' => 'Mingguan (8 Minggu)', 'monthly' => 'Bulanan (6 Bulan)'] as $key => $label)
                    <a href="{{ route('reports.index', ['period' => $key]) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $period === $key ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:bg-indigo-50 hover:text-indigo-600' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        {{-- KPI Cards --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-sm shadow-indigo-100/50">
                <div class="text-xs font-semibold text-slate-500">Pendapatan Periode</div>
                <div class="mt-2 text-lg font-extrabold text-indigo-600">Rp {{ number_format($periodRevenue, 0, ',', '.') }}</div>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-sm shadow-indigo-100/50">
                <div class="text-xs font-semibold text-slate-500">Total Transaksi</div>
                <div class="mt-2 text-lg font-extrabold text-slate-900">{{ number_format($periodTransactions) }} nota</div>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-sm shadow-indigo-100/50">
                <div class="text-xs font-semibold text-slate-500">Rata-rata Nilai Nota</div>
                <div class="mt-2 text-lg font-extrabold text-slate-900">Rp {{ number_format($averageBasket, 0, ',', '.') }}</div>
            </div>
            <div class="rounded-2xl bg-white border border-slate-200/80 p-4 shadow-sm shadow-indigo-100/50">
                <div class="text-xs font-semibold text-slate-500">Diskon Poin Diberikan</div>
                <div class="mt-2 text-lg font-extrabold text-emerald-600">Rp {{ number_format($totalDiscountGiven, 0, ',', '.') }}</div>
            </div>
        </section>

        {{-- Revenue Trend Chart --}}
        <section class="rounded-2xl bg-white border border-slate-200/80 p-5 shadow-sm shadow-indigo-100/50">
            <h2 class="text-base font-bold text-slate-900 mb-1">Grafik Tren Pendapatan</h2>
            <p class="text-xs text-slate-500 mb-4">Total omzet bersih berdasarkan periode yang dipilih</p>
            <div class="h-72">
                <canvas id="revenueAnalyticsChart"></canvas>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Top 10 Spenders --}}
            <section class="rounded-2xl bg-white border border-slate-200/80 shadow-sm shadow-indigo-100/50 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Top 10 Spenders (Pelanggan Paling Loyal)</h2>
                    <p class="text-xs text-slate-500">Berdasarkan total pengeluaran &amp; frekuensi kunjungan</p>
                </div>

                @if($topSpenders->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach($topSpenders as $index => $spender)
                            <a href="{{ route('customers.show', $spender) }}" class="px-5 py-3.5 flex items-center justify-between gap-3 hover:bg-indigo-50/50 transition">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="w-7 h-7 rounded-xl text-xs font-extrabold flex items-center justify-center shrink-0 {{ $index < 3 ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' }}">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="min-w-0">
                                        <div class="text-sm font-bold text-slate-900 truncate">{{ $spender->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $spender->visits_count }} kunjungan &bull; {{ number_format($spender->total_points) }} poin</div>
                                    </div>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="text-sm font-extrabold text-indigo-600">Rp {{ number_format((float)$spender->total_spent, 0, ',', '.') }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="px-5 py-10 text-center text-sm text-slate-500">Belum ada data pelanggan.</div>
                @endif
            </section>

            {{-- Service Performance --}}
            <section class="rounded-2xl bg-white border border-slate-200/80 shadow-sm shadow-indigo-100/50 overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="text-base font-bold text-slate-900">Analisis Layanan Terlaris</h2>
                    <p class="text-xs text-slate-500">Peringkat layanan dengan omzet &amp; kuantitas tertinggi pada periode ini</p>
                </div>

                @if(! empty($servicePerformance))
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50/80 border-b border-slate-100 text-[11px] font-semibold uppercase tracking-wider text-slate-500">
                                    <th class="px-5 py-3">Layanan</th>
                                    <th class="px-5 py-3 text-center">Terjual</th>
                                    <th class="px-5 py-3 text-right">Total Omzet</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-sm">
                                @foreach($servicePerformance as $srv)
                                    <tr class="hover:bg-indigo-50/50 transition">
                                        <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $srv['name'] }}</td>
                                        <td class="px-5 py-3.5 text-center">
                                            <span class="px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold">{{ $srv['qty'] }}x</span>
                                        </td>
                                        <td class="px-5 py-3.5 text-right font-bold text-slate-900">
                                            Rp {{ number_format($srv['revenue'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="px-5 py-10 text-center text-sm text-slate-500">Belum ada data penjualan layanan pada periode ini.</div>
                @endif
            </section>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const canvas = document.getElementById('revenueAnalyticsChart');
                if (!canvas || typeof window.Chart === 'undefined') return;

                new window.Chart(canvas, {
                    type: 'line',
                    data: {
                        labels: @json($chartLabels),
                        datasets: [{
                            label: 'Pendapatan (Rp)',
                            data: @json($chartRevenues),
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.12)',
                            fill: true,
                            tension: 0.35,
                            borderWidth: 2.5,
                            pointBackgroundColor: '#4f46e5',
                            pointRadius: 4,
                        }]
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
                                callbacks: {
                                    label: (ctx) => 'Rp ' + Number(ctx.parsed.y || 0).toLocaleString('id-ID')
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    color: '#64748b',
                                    callback: (val) => 'Rp ' + Number(val).toLocaleString('id-ID')
                                },
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
