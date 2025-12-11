<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Celengan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nama',
        'currency',
        'target',
        'nominal_terkumpul',
        'nominal_pengisian',
        'plan',
        'notif_on',
        'notif_day',
        'notif_time',
        'image_path',
        'target_date',
        'completed_at',
        'status', 
    ];

    protected $casts = [
        'target_date' => 'datetime',
        'completed_at' => 'datetime',
        'notif_on' => 'boolean',
    ];

    protected $appends = [
        'image_url',
        'total_deposit',
        'total_withdraw',
    ];

    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? asset('api/image-proxy/' . $this->image_path)
            : null;
    }

    public function transactions()
    {
        return $this->hasMany(CelenganTransaction::class);
    }

    public function getTotalDepositAttribute(): int
    {
        if (! array_key_exists('total_deposit', $this->attributes)) {
            $this->loadMissing(['transactions' => function ($query) {
                $query->select('celengan_id', 'type', 'amount');
            }]);
            $this->attributes['total_deposit'] = $this->transactions
                ->where('type', 'deposit')
                ->sum('amount');
        }

        return (int) ($this->attributes['total_deposit'] ?? 0);
    }

    public function getTotalWithdrawAttribute(): int
    {
        if (! array_key_exists('total_withdraw', $this->attributes)) {
            $this->loadMissing(['transactions' => function ($query) {
                $query->select('celengan_id', 'type', 'amount');
            }]);
            $this->attributes['total_withdraw'] = $this->transactions
                ->where('type', 'withdraw')
                ->sum('amount');
        }

        return (int) ($this->attributes['total_withdraw'] ?? 0);
    }
}
