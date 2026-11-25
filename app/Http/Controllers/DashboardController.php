<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->startOfDay();
        $thirtyDaysAgo = now()->subDays(30);

        $activeCustomersCount = Customer::query()
            ->where(function ($query) use ($thirtyDaysAgo): void {
                $query->where('last_visit_at', '>=', $thirtyDaysAgo)
                    ->orWhere(function ($sub) use ($thirtyDaysAgo): void {
                        $sub->whereNull('last_visit_at')
                            ->where('created_at', '>=', $thirtyDaysAgo);
                    });
            })
            ->count();

        $totalCustomersCount = Customer::count();

        $todayStats = Transaction::query()
            ->where('created_at', '>=', $today)
            ->selectRaw('COUNT(*) as tx_count, COALESCE(SUM(total_paid), 0) as total_revenue')
            ->first();

        $todayTransactionsCount = (int) ($todayStats->tx_count ?? 0);
        $todayRevenue = (float) ($todayStats->total_revenue ?? 0);

        // Weekly visit & revenue trend (last 7 days)
        $startDate = now()->subDays(6)->startOfDay();
        $dailyRows = Transaction::query()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as tx_date, COUNT(*) as visits, COALESCE(SUM(total_paid), 0) as revenue')
            ->groupBy('tx_date')
            ->get()
            ->keyBy('tx_date');

        $chartLabels = [];
        $chartVisits = [];
        $chartRevenues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $key = $date->format('Y-m-d');
            $row = $dailyRows->get($key);
            $chartLabels[] = $date->translatedFormat('D, d M');
            $chartVisits[] = (int) ($row->visits ?? 0);
            $chartRevenues[] = (float) ($row->revenue ?? 0);
        }

        $recentTransactions = Transaction::query()
            ->with(['customer:id,name,whatsapp_number,total_points', 'user:id,name'])
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard', [
            'activeCustomersCount' => $activeCustomersCount,
            'totalCustomersCount' => $totalCustomersCount,
            'todayTransactionsCount' => $todayTransactionsCount,
            'todayRevenue' => $todayRevenue,
            'chartLabels' => $chartLabels,
            'chartVisits' => $chartVisits,
            'chartRevenues' => $chartRevenues,
            'recentTransactions' => $recentTransactions,
        ]);
    }
}
