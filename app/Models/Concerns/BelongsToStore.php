<?php

namespace App\Models\Concerns;

use App\Models\Scopes\StoreScope;
use App\Models\Store;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToStore
{
    protected static function bootBelongsToStore(): void
    {
        static::addGlobalScope(new StoreScope());

        static::creating(function ($model): void {
            if (empty($model->store_id) && auth()->hasUser()) {
                $user = auth()->user();
                if ($user && $user->store_id) {
                    $model->store_id = $user->store_id;
                }
            }
        });
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
