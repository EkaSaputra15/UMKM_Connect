<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->query('period', 'daily');
        if (! in_array($period, ['daily', 'weekly', 'monthly'], true)) {
            $period = 'daily';
        }

        $rangeStart = match ($period) {
            'weekly' => now()->subWeeks(7)->startOfWeek(),
            'monthly' => now()->subMonths(5)->startOfMonth(),
            default => now()->subDays(13)->startOfDay(),
        };

        $transactionsInRange = Transaction::query()
            ->where('created_at', '>=', $rangeStart)
            ->select(['id', 'total_paid', 'discount_amount', 'points_used', 'points_earned', 'service_detail', 'created_at'])
            ->get();

        // Build Revenue Chart Series
        $buckets = [];
        if ($period === 'daily') {
            for ($i = 13; $i >= 0; $i--) {
                $d = Carbon::now()->subDays($i);
                $buckets[$d->format('Y-m-d')] = [
                    'label' => $d->translatedFormat('d M'),
                    'revenue' => 0.0,
                    'count' => 0,
                ];
            }
            foreach ($transactionsInRange as $tx) {
                $key = $tx->created_at->format('Y-m-d');
                if (isset($buckets[$key])) {
                    $buckets[$key]['revenue'] += (float) $tx->total_paid;
                    $buckets[$key]['count']++;
                }
            }
        } elseif ($period === 'weekly') {
            for ($i = 7; $i >= 0; $i--) {
                $w = Carbon::now()->subWeeks($i)->startOfWeek();
                $key = $w->format('o-W');
                $buckets[$key] = [
                    'label' => 'Minggu ' . $w->format('d M'),
                    'revenue' => 0.0,
                    'count' => 0,
                ];
            }
            foreach ($transactionsInRange as $tx) {
                $key = $tx->created_at->format('o-W');
                if (isset($buckets[$key])) {
                    $buckets[$key]['revenue'] += (float) $tx->total_paid;
                    $buckets[$key]['count']++;
                }
            }
        } else {
            for ($i = 5; $i >= 0; $i--) {
                $m = Carbon::now()->subMonths($i)->startOfMonth();
                $key = $m->format('Y-m');
                $buckets[$key] = [
                    'label' => $m->translatedFormat('M Y'),
                    'revenue' => 0.0,
                    'count' => 0,
                ];
            }
            foreach ($transactionsInRange as $tx) {
                $key = $tx->created_at->format('Y-m');
                if (isset($buckets[$key])) {
                    $buckets[$key]['revenue'] += (float) $tx->total_paid;
                    $buckets[$key]['count']++;
                }
            }
        }

        $chartLabels = array_values(array_map(fn ($b) => $b['label'], $buckets));
        $chartRevenues = array_values(array_map(fn ($b) => $b['revenue'], $buckets));
        $chartCounts = array_values(array_map(fn ($b) => $b['count'], $buckets));

        $periodRevenue = (float) $transactionsInRange->sum('total_paid');
        $periodTransactions = $transactionsInRange->count();
        $averageBasket = $periodTransactions > 0 ? ($periodRevenue / $periodTransactions) : 0;
        $totalDiscountGiven = (float) $transactionsInRange->sum('discount_amount');

        // Top 10 Spenders (single aggregated query, no N+1)
        $topSpenders = Customer::query()
            ->select(
                'customers.id',
                'customers.name',
                'customers.whatsapp_number',
                'customers.email',
                'customers.total_points',
                'customers.last_visit_at'
            )
            ->selectRaw('COALESCE(SUM(transactions.total_paid), 0) as total_spent, COUNT(transactions.id) as visits_count')
            ->leftJoin('transactions', 'customers.id', '=', 'transactions.customer_id')
            ->groupBy(
                'customers.id',
                'customers.name',
                'customers.whatsapp_number',
                'customers.email',
                'customers.total_points',
                'customers.last_visit_at'
            )
            ->orderByDesc('total_spent')
            ->orderByDesc('visits_count')
            ->limit(10)
            ->get();

        // Service performance from transaction snapshots
        $allTransactionsForServices = Transaction::query()
            ->where('created_at', '>=', $rangeStart)
            ->select(['service_detail'])
            ->get();

        $servicePerformance = [];
        foreach ($allTransactionsForServices as $tx) {
            foreach ($tx->service_detail ?? [] as $item) {
                $name = $item['name'] ?? 'Layanan Lainnya';
                $qty = (int) ($item['qty'] ?? 1);
                $sub = (float) ($item['subtotal'] ?? 0);

                if (! isset($servicePerformance[$name])) {
                    $servicePerformance[$name] = [
                        'name' => $name,
                        'qty' => 0,
                        'revenue' => 0.0,
                        'orders' => 0,
                    ];
                }

                $servicePerformance[$name]['qty'] += $qty;
                $servicePerformance[$name]['revenue'] += $sub;
                $servicePerformance[$name]['orders'] += 1;
            }
        }

        uasort($servicePerformance, fn ($a, $b) => $b['revenue'] <=> $a['revenue']);

        return view('reports.index', [
            'period' => $period,
            'chartLabels' => $chartLabels,
            'chartRevenues' => $chartRevenues,
            'chartCounts' => $chartCounts,
            'periodRevenue' => $periodRevenue,
            'periodTransactions' => $periodTransactions,
            'averageBasket' => $averageBasket,
            'totalDiscountGiven' => $totalDiscountGiven,
            'topSpenders' => $topSpenders,
            'servicePerformance' => array_slice(array_values($servicePerformance), 0, 10),
        ]);
    }
}
