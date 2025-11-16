<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NootPro\SubscriptionPlans\Models\Plan;

trait BelongsToPlan
{
    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id', 'plan');
    }

    /**
     * Scope models by plan id.
     *
     * @param  Builder<Model>  $builder
     * @return Builder<Model>
     */
    public function scopeByPlanId(Builder $builder, int $planId): Builder
    {
        return $builder->where('plan_id', $planId);
    }
}
