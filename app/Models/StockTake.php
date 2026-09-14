<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTake extends Model
{
    use HasFactory, BelongsToBusiness;
    protected $fillable = ['business_id','reference','status','counted_by','approved_by','posted_at'];
    protected $casts = ['posted_at' => 'datetime'];
    public function items()    { return $this->hasMany(StockTakeItem::class); }
    public function counter()  { return $this->belongsTo(User::class, 'counted_by'); }
}
