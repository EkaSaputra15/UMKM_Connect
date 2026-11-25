<?php

namespace App\Console\Commands;

use App\Jobs\SendBroadcastEmailJob;
use App\Models\Broadcast;
use Illuminate\Console\Command;

class ProcessScheduledBroadcasts extends Command
{
    protected $signature = 'broadcasts:process-scheduled';

    protected $description = 'Dispatch scheduled email broadcast campaigns that are due.';

    public function handle(): int
    {
        $dueBroadcasts = Broadcast::withoutGlobalScopes()
            ->where('status', 'pending')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($dueBroadcasts as $broadcast) {
            SendBroadcastEmailJob::dispatch($broadcast->id);
        }

        $this->info("Dispatched {$dueBroadcasts->count()} scheduled broadcast(s).");

        return self::SUCCESS;
    }
}
