<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * PlanSubscriptionFeature.
 *
 * @property int $subscription_id
 * @property int $feature_id
 * @property int $quantity
 * @property string $source
 * @property-read PlanFeature $feature
 * @property-read PlanSubscription $subscription
 */
class PlanSubscriptionFeature extends Model
{
    use SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('subscription-plans.table_names.plan_subscription_features', 'plan_subscription_features');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'subscription_id',
        'feature_id',
        'quantity',
        'source',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
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
}
