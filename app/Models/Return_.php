<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Return_ extends Model
{
    use HasFactory, BelongsToBusiness;
    protected $table = 'returns';
    protected $fillable = ['business_id','type','reference','sale_id','purchase_id','item','reason','resolution','amount','approval_status'];
    protected $casts = ['amount' => 'decimal:2'];
    public function sale()     { return $this->belongsTo(Sale::class); }
    public function purchase() { return $this->belongsTo(Purchase::class); }
}
