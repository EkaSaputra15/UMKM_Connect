<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function show(Customer $customer): View
    {
        $customer->load('store');

        $transactions = $customer->transactions()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);

        $totalSpent = (float) $customer->transactions()->sum('total_paid');
        $visitCount = $customer->transactions()->count();

        return view('customers.show', [
            'customer' => $customer,
            'transactions' => $transactions,
            'totalSpent' => $totalSpent,
            'visitCount' => $visitCount,
        ]);
    }
}
