<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobPart extends Model
{
    use HasFactory;

    protected $fillable = ['job_card_id', 'product_id', 'name', 'qty', 'unit_cost'];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'qty'       => 'integer',
    ];

    public function jobCard()
    {
        return $this->belongsTo(JobCard::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
