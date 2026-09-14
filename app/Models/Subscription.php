<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'plan', 'monthly_fee',
        'status', 'starts_at', 'ends_at', 'trial_ends_at',
    ];

    protected $casts = [
        'starts_at'      => 'date',
        'ends_at'        => 'date',
        'trial_ends_at'  => 'date',
        'monthly_fee'    => 'decimal:2',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}