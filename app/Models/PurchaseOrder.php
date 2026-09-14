<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id', 'reference', 'supplier_id',
        'order_date', 'expected_date', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'order_date'    => 'date',
        'expected_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getEstimatedTotalAttribute(): float
    {
        return (float) $this->items->sum(fn ($i) => $i->qty * (float) $i->unit_cost);
    }
}
