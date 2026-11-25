<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'business_type',
        'address',
        'logo_path',
        'sender_name',
        'sender_email',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'point_value_idr',
        'vip_points_threshold',
    ];

    protected $hidden = [
        'smtp_password',
    ];

    protected function casts(): array
    {
        return [
            'smtp_port' => 'integer',
            'smtp_password' => 'encrypted',
            'point_value_idr' => 'integer',
            'vip_points_threshold' => 'integer',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function broadcasts(): HasMany
    {
        return $this->hasMany(Broadcast::class);
    }

    public function hasCustomSmtp(): bool
    {
        return ! empty($this->smtp_host)
            && ! empty($this->smtp_port)
            && ! empty($this->smtp_username)
            && ! empty($this->smtp_password);
    }

    public function effectiveSenderName(): string
    {
        return $this->sender_name ?: $this->name;
    }

    public function effectiveSenderEmail(): string
    {
        return $this->sender_email ?: config('mail.from.address', 'noreply@umkmconnect.id');
    }
}
