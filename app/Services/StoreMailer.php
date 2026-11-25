<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;

class StoreMailer
{
    public function send(Store $store, string $recipientEmail, Mailable $mailable): void
    {
        $mailable->from($store->effectiveSenderEmail(), $store->effectiveSenderName());

        if ($store->hasCustomSmtp() && config('mail.default') !== 'array') {
            $scheme = ($store->smtp_encryption === 'ssl' || (int) $store->smtp_port === 465)
                ? 'smtps'
                : 'smtp';

            $mailer = Mail::build([
                'transport' => 'smtp',
                'scheme' => $scheme,
                'host' => $store->smtp_host,
                'port' => $store->smtp_port,
                'username' => $store->smtp_username,
                'password' => $store->smtp_password,
                'timeout' => 15,
            ]);

            $mailer->to($recipientEmail)->send($mailable);

            return;
        }

        Mail::to($recipientEmail)->send($mailable);
    }
}
