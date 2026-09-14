<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobCard extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $fillable = [
        'business_id', 'job_no', 'customer_id', 'device', 'serial',
        'issue', 'diagnosis', 'status', 'assigned_to',
        'quoted_amount', 'deposit', 'revenue', 'labour_cost', 'warranty',
    ];

    protected $casts = [
        'quoted_amount' => 'decimal:2',
        'deposit'       => 'decimal:2',
        'revenue'       => 'decimal:2',
        'labour_cost'   => 'decimal:2',
    ];

    /* Statuses matching the prototype */
    public static array $statuses = [
        'received'           => 'Received',
        'diagnosing'         => 'Diagnosing',
        'awaiting_approval'  => 'Awaiting approval',
        'in_repair'          => 'In repair',
        'ready'              => 'Ready for collection',
        'collected'          => 'Collected',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignee()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function parts()
    {
        return $this->hasMany(JobPart::class);
    }

    public function history()
    {
        return $this->hasMany(JobStatusHistory::class)->orderByDesc('created_at');
    }

    /* ── Financial rollup ─────────────────────────────── */

    public function getPartsCostAttribute(): float
    {
        return (float) $this->parts->sum(fn ($p) => $p->qty * (float) $p->unit_cost);
    }

    public function getOtherCostAttribute(): float
    {
        return 0.0; // reserved for future expense tracking
    }

    public function getTotalExpenseAttribute(): float
    {
        return $this->parts_cost + (float) $this->labour_cost + $this->other_cost;
    }

    public function getProfitAttribute(): float
    {
        return (float) $this->revenue - $this->total_expense;
    }
}
