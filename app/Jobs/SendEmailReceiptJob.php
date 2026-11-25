<?php

namespace App\Jobs;

use App\Mail\DigitalReceiptMail;
use App\Models\Transaction;
use App\Services\StoreMailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendEmailReceiptJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $transactionId)
    {
    }

    public function handle(StoreMailer $mailer): void
    {
        $transaction = Transaction::withoutGlobalScopes()
            ->with(['store', 'customer', 'user'])
            ->find($this->transactionId);

        if (! $transaction || ! $transaction->store || ! $transaction->customer) {
            return;
        }

        if (empty($transaction->customer->email)) {
            return;
        }

        $mailer->send(
            $transaction->store,
            $transaction->customer->email,
            new DigitalReceiptMail($transaction)
        );

        $transaction->forceFill(['receipt_sent_at' => now()])->save();
    }
}
