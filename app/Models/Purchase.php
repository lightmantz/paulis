<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id', 'reference', 'supplier_id', 'purchase_order_id',
        'purchase_date', 'subtotal', 'expenses_total', 'total',
        'paid', 'balance', 'status', 'notes',
    ];

    protected $casts = [
        'purchase_date'  => 'date',
        'subtotal'       => 'decimal:2',
        'expenses_total' => 'decimal:2',
        'total'          => 'decimal:2',
        'paid'           => 'decimal:2',
        'balance'        => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function expenses()
    {
        return $this->hasMany(PurchaseExpense::class);
    }
}
