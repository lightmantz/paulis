<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id', 'sku', 'barcode', 'name', 'category_id', 'group_id',
        'condition', 'specs', 'tracking', 'stock', 'reorder_level',
        'unit_cost', 'selling_price', 'supplier_id', 'created_by',
    ];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];
}