<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use BelongsToStore, HasFactory;

    protected $fillable = [
        'store_id',
        'customer_id',
        'user_id',
        'invoice_code',
        'service_detail',
        'subtotal',
        'points_used',
        'discount_amount',
        'total_paid',
        'points_earned',
        'receipt_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'service_detail' => 'array',
            'subtotal' => 'decimal:2',
            'points_used' => 'integer',
            'discount_amount' => 'decimal:2',
            'total_paid' => 'decimal:2',
            'points_earned' => 'integer',
            'receipt_sent_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateInvoiceCode(int $storeId): string
    {
        return sprintf(
            'INV-%d-%s-%s',
            $storeId,
            now()->format('YmdHis'),
            strtoupper(Str::random(4))
        );
    }
}
