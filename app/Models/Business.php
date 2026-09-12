<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name', 'owner_name', 'email', 'phone', 'city',
        'address', 'tin', 'logo', 'plan', 'status',
        'monthly_fee', 'renewal_date', 'trial_ends_at',
    ];

    protected $casts = [
        'renewal_date'  => 'date',
        'trial_ends_at' => 'datetime',
        'monthly_fee'   => 'decimal:2',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class)
                    ->whereIn('status', ['Trial', 'Active'])
                    ->latestOfMany();
    }
}
