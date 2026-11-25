<?php

namespace App\Jobs;

use App\Mail\BroadcastPromoMail;
use App\Models\Broadcast;
use App\Models\Customer;
use App\Services\StoreMailer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class SendBroadcastEmailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public int $broadcastId)
    {
    }

    public function handle(StoreMailer $mailer): void
    {
        $broadcast = Broadcast::withoutGlobalScopes()
            ->with('store')
            ->find($this->broadcastId);

        if (! $broadcast || ! $broadcast->store) {
            return;
        }

        $store = $broadcast->store;
        $vipThreshold = $store->vip_points_threshold ?? 1000;
        $sentCount = 0;

        try {
            Customer::withoutGlobalScopes()
                ->where('store_id', $store->id)
                ->emailable()
                ->segment($broadcast->target_segment, $vipThreshold)
                ->chunkById(100, function ($customers) use ($mailer, $store, $broadcast, &$sentCount): void {
                    foreach ($customers as $customer) {
                        $mailer->send(
                            $store,
                            $customer->email,
                            new BroadcastPromoMail($broadcast, $customer)
                        );
                        $sentCount++;
                    }
                });

            $broadcast->forceFill([
                'status' => 'sent',
                'sent_at' => now(),
                'recipients_count' => $sentCount,
                'error_message' => null,
            ])->save();
        } catch (Throwable $e) {
            $broadcast->forceFill([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 1000),
            ])->save();

            throw $e;
        }
    }
}
