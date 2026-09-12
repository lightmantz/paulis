<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id', 'name', 'contact_person', 'phone', 'email',
        'address', 'balance',
    ];
}