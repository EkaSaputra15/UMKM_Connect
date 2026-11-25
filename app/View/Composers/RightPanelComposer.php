<?php

namespace App\View\Composers;

use App\Models\Transaction;
use Illuminate\View\View;

class RightPanelComposer
{
    public function compose(View $view): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $store = $user->store;
        $latestPanelTransactions = Transaction::query()
            ->with('customer:id,name')
            ->latest()
            ->limit(4)
            ->get(['id', 'customer_id', 'invoice_code', 'total_paid', 'points_earned', 'receipt_sent_at', 'created_at']);

        $smtpConfigured = $store?->hasCustomSmtp() ?? false;
        $systemMailer = config('mail.default', 'log');

        $view->with([
            'panelStore' => $store,
            'latestPanelTransactions' => $latestPanelTransactions,
            'panelEmailStatus' => [
                'custom_smtp' => $smtpConfigured,
                'mailer' => $smtpConfigured ? 'SMTP Toko' : strtoupper($systemMailer),
                'sender_email' => $store?->effectiveSenderEmail() ?? config('mail.from.address'),
                'active' => $smtpConfigured || $systemMailer !== 'log',
            ],
        ]);
    }
}
