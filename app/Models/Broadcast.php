<?php

namespace App\Models;

use App\Models\Concerns\BelongsToStore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Broadcast extends Model
{
    use BelongsToStore, HasFactory;

    public const SEGMENT_ALL = 'all';
    public const SEGMENT_INACTIVE_30_DAYS = 'inactive_30_days';
    public const SEGMENT_VIP = 'vip';

    protected $fillable = [
        'store_id',
        'subject',
        'message',
        'target_segment',
        'scheduled_at',
        'sent_at',
        'recipients_count',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'sent_at' => 'datetime',
            'recipients_count' => 'integer',
        ];
    }

    public static function segmentLabels(): array
    {
        return [
            self::SEGMENT_ALL => 'Semua Pelanggan (Memiliki Email)',
            self::SEGMENT_INACTIVE_30_DAYS => 'Pelanggan Pasif (> 30 Hari Tidak Berkunjung)',
            self::SEGMENT_VIP => 'Pelanggan VIP (Poin Tinggi)',
        ];
    }

    public function segmentLabel(): string
    {
        return self::segmentLabels()[$this->target_segment] ?? $this->target_segment;
    }
}
