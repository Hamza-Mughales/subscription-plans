<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PlanSubscriptionUsage.
 *
 * @property int $subscription_id
 * @property int $used
 * @property Carbon|null $valid_until
 * @property Carbon|null $expires_at Alias for valid_until
 * @property-read PlanFeature      $feature
 * @property-read PlanSubscription $subscription
 *
 * @method static Builder|PlanSubscriptionUsage byFeatureCode($featureCode)
 * @method static Builder|PlanSubscriptionUsage whereCreatedAt($value)
 * @method static Builder|PlanSubscriptionUsage whereDeletedAt($value)
 * @method static Builder|PlanSubscriptionUsage whereFeatureId($value)
 * @method static Builder|PlanSubscriptionUsage whereId($value)
 * @method static Builder|PlanSubscriptionUsage whereSubscriptionId($value)
 * @method static Builder|PlanSubscriptionUsage whereUpdatedAt($value)
 * @method static Builder|PlanSubscriptionUsage whereUsed($value)
 * @method static Builder|PlanSubscriptionUsage whereValidUntil($value)
 */
class PlanSubscriptionUsage extends Model
{
    use SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('subscription-plans.table_names.plan_subscription_usage', 'plan_subscription_usage');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'subscription_id',
        'feature_id',
        'used',
        'valid_until',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'used'        => 'integer',
        'valid_until' => 'datetime',
    ];

    /**
     * @return BelongsTo<PlanFeature, $this>
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(PlanFeature::class, 'feature_id', 'id', 'feature');
    }

    /**
     * @return BelongsTo<PlanSubscription, $this>
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(PlanSubscription::class, 'subscription_id', 'id', 'subscription');
    }

    /**
     * Scope subscription usage by feature code.
     *
     * @param  Builder<PlanSubscription>  $builder
     * @return Builder<PlanSubscription>
     */
    public function scopeByFeatureCode(Builder $builder, string $featureCode, int $planId): Builder
    {
        $feature = \HamzaMughales\Subscriptions\Models\PlanFeature::where('plan_id', $planId)->where('code', $featureCode)->firstOrFail();

        return $builder->where('feature_id', $feature->getKey());
    }

    public function expired(): bool
    {
        if (! $this->valid_until) {
            return false;
        }

        return Carbon::now()->gte($this->valid_until);
    }
}
