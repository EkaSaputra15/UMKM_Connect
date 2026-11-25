<?php

namespace App\Http\Controllers;

use App\Jobs\SendEmailReceiptJob;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $transactions = Transaction::query()
            ->with(['customer:id,name,whatsapp_number,email', 'user:id,name'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($sub) use ($search): void {
                    $sub->where('invoice_code', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($cq) use ($search): void {
                            $cq->where('name', 'like', "%{$search}%")
                                ->orWhere('whatsapp_number', 'like', "%{$search}%");
                        });
                });
            })
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('transactions.index', [
            'transactions' => $transactions,
            'search' => $search,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['store', 'customer', 'user']);

        return view('transactions.show', [
            'transaction' => $transaction,
        ]);
    }

    public function resendReceipt(Transaction $transaction): RedirectResponse
    {
        $transaction->load('customer');

        if (! $transaction->customer || empty($transaction->customer->email)) {
            return back()->with('error', 'Pelanggan pada transaksi ini belum memiliki alamat email.');
        }

        SendEmailReceiptJob::dispatch($transaction->id);

        return back()->with('success', "Nota digital {$transaction->invoice_code} sedang dikirim ulang ke {$transaction->customer->email}.");
    }
}
