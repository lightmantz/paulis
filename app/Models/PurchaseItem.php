<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = ['purchase_id', 'product_id', 'qty', 'unit_cost', 'line_total'];

    protected $casts = [
        'qty'        => 'integer',
        'unit_cost'  => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function product() { return $this->belongsTo(Product::class); }
}
