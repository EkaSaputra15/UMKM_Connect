<?php

namespace App\Mail;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DigitalReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Transaction $transaction)
    {
    }

    public function envelope(): Envelope
    {
        $storeName = $this->transaction->store?->name ?? 'UMKM Connect';

        return new Envelope(
            subject: "Nota Digital {$this->transaction->invoice_code} - {$storeName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.receipt',
            with: [
                'transaction' => $this->transaction,
                'store' => $this->transaction->store,
                'customer' => $this->transaction->customer,
                'cashier' => $this->transaction->user,
            ],
        );
    }
}
