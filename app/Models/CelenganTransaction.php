<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CelenganTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'celengan_id',
        'user_id',
        'type',
        'amount',
        'description',
        'created_at',
    ];

    public function celengan()
    {
        return $this->belongsTo(Celengan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


