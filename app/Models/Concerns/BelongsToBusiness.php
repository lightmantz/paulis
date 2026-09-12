<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToBusiness
{
    protected static function bootBelongsToBusiness(): void
    {
        // Scope all queries to the current business
        static::addGlobalScope('business', function (Builder $query) {
            if (auth()->guard('web')->check() && auth()->guard('web')->user()->business_id) {
                $query->where(
                    $query->getModel()->getTable() . '.business_id',
                    auth()->guard('web')->user()->business_id
                );
            }
        });

        // Auto-fill business_id on create
        static::creating(function ($model) {
            if (empty($model->business_id)
                && auth()->guard('web')->check()
                && auth()->guard('web')->user()->business_id) {
                $model->business_id = auth()->guard('web')->user()->business_id;
            }
        });
    }

    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class);
    }
}