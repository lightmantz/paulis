<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id', 'name', 'phone', 'email', 'type',
        'address', 'credit_allowed', 'balance',
    ];
}