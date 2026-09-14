<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'display_name', 'tin', 'address', 'city', 'phone', 'email',
        'logo_path', 'receipt_footer', 'invoice_prefix', 'receipt_prefix',
        'quotation_prefix', 'currency', 'vat_rate', 'default_reorder_level',
        'require_owner_discount_approval', 'max_sales_discount_percent',
        'payment_accounts',
    ];

    protected $casts = [
        'vat_rate' => 'decimal:2',
        'max_sales_discount_percent' => 'decimal:2',
        'require_owner_discount_approval' => 'boolean',
        'payment_accounts' => 'array',
    ];

    public function business() { return $this->belongsTo(Business::class); }
}
