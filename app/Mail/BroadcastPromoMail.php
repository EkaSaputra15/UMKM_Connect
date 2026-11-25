<?php

namespace App\Mail;

use App\Models\Broadcast;
use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BroadcastPromoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Broadcast $broadcast,
        public Customer $customer,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->broadcast->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.broadcast',
            with: [
                'broadcast' => $this->broadcast,
                'store' => $this->broadcast->store,
                'customer' => $this->customer,
            ],
        );
    }
}
