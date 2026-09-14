<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTakeItem extends Model
{
    use HasFactory;
    protected $fillable = ['stock_take_id','product_id','book_qty','physical_qty','variance','unit_cost','variance_value','serial_ids','status'];
    protected $casts = ['serial_ids' => 'array', 'unit_cost' => 'decimal:2', 'variance_value' => 'decimal:2'];
    public function stockTake() { return $this->belongsTo(StockTake::class); }
    public function product()   { return $this->belongsTo(Product::class); }
}
