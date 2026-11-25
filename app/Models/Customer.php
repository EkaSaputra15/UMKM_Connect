<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use BelongsToStore, HasFactory;

    protected $fillable = [
        'store_id',
        'name',
        'whatsapp_number',
        'email',
        'birth_date',
        'total_points',
        'last_visit_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'total_points' => 'integer',
            'last_visit_at' => 'datetime',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function getLoyaltyTierAttribute(): string
    {
        $vipThreshold = $this->store?->vip_points_threshold ?? 1000;

        if ($this->total_points >= $vipThreshold) {
            return 'VIP';
        }

        if ($this->total_points >= 300) {
            return 'Silver';
        }

        return 'Bronze';
    }

    public function scopeSegment(Builder $query, string $segment, int $vipThreshold = 1000): Builder
    {
        return match ($segment) {
            'inactive_30_days' => $query->where(function (Builder $q): void {
                $cutoff = now()->subDays(30);
                $q->where('last_visit_at', '<=', $cutoff)
                    ->orWhere(function (Builder $sub) use ($cutoff): void {
                        $sub->whereNull('last_visit_at')->where('created_at', '<=', $cutoff);
                    });
            }),
            'vip' => $query->where('total_points', '>=', $vipThreshold),
            default => $query,
        };
    }

    public function scopeEmailable(Builder $query): Builder
    {
        return $query->whereNotNull('email')->where('email', '!=', '');
    }

    public static function normalizeWhatsapp(string $number): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $number) ?? '';
        if (str_starts_with($cleaned, '0')) {
            return '62' . substr($cleaned, 1);
        }

        return $cleaned;
    }
}
