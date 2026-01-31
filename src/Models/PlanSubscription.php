<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use LogicException;
use HamzaMughales\Subscriptions\Database\Factories\PlanSubscriptionFactory;
use HamzaMughales\Subscriptions\Enums\Interval;
use HamzaMughales\Subscriptions\Enums\SubscriptionModel;
use HamzaMughales\Subscriptions\Events\SubscriptionDeleted;
use HamzaMughales\Subscriptions\Services\Period;
use HamzaMughales\Subscriptions\Traits\BelongsToPlan;
use HamzaMughales\Subscriptions\Traits\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

/**
 * PlanSubscription.
 *
 * @property int $plan_id
 * @property int $subscriber_id
 * @property string $subscriber_type
 * @property bool $is_active
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $starts_at
 * @property Carbon|null $ends_at
 * @property Carbon|null $cancels_at
 * @property Carbon|null $canceled_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Plan $plan
 * @property-read Collection|PlanSubscriptionUsage[] $usage
 * @property-read Model $subscriber
 *
 * @method static Builder|PlanSubscription byPlanId($planId)
 * @method static Builder|PlanSubscription findEndedPeriod()
 * @method static Builder|PlanSubscription findEndedTrial()
 * @method static Builder|PlanSubscription findEndingPeriod($dayRange = 3)
 * @method static Builder|PlanSubscription findEndingTrial($dayRange = 3)
 * @method static Builder|PlanSubscription ofSubscriber(Model $subscriber)
 * @method static Builder|PlanSubscription whereCanceledAt($value)
 * @method static Builder|PlanSubscription whereCancelsAt($value)
 * @method static Builder|PlanSubscription whereCreatedAt($value)
 * @method static Builder|PlanSubscription whereDeletedAt($value)
 * @method static Builder|PlanSubscription whereDescription($value)
 * @method static Builder|PlanSubscription whereEndsAt($value)
 * @method static Builder|PlanSubscription whereId($value)
 * @method static Builder|PlanSubscription whereTitle($value)
 * @method static Builder|PlanSubscription wherePlanId($value)
 * @method static Builder|PlanSubscription whereSlug($value)
 * @method static Builder|PlanSubscription whereStartsAt($value)
 * @method static Builder|PlanSubscription whereTrialEndsAt($value)
 * @method static Builder|PlanSubscription whereUpdatedAt($value)
 * @method static Builder|PlanSubscription whereSubscriberId($value)
 * @method static Builder|PlanSubscription whereSubscriberType($value)
 */
class PlanSubscription extends Model
{
    use BelongsToPlan;

    /** @use HasFactory<PlanSubscriptionFactory> */
    use HasFactory;

    use HasSlug;
    use HasTranslations;
    use SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('subscription-plans.table_names.plan_subscriptions', 'plan_subscriptions');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'subscriber_id',
        'subscriber_type',
        'plan_id',
        'slug',
        'name',
        'description',
        'subscription_type',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'cancels_at',
        'canceled_at',
        'timezone',
        'is_active',
        'is_paid',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'subscription_type' => SubscriptionModel::class,
        'trial_ends_at'     => 'datetime',
        'starts_at'         => 'datetime',
        'ends_at'           => 'datetime',
        'cancels_at'        => 'datetime',
        'canceled_at'       => 'datetime',
        'is_active'         => 'boolean',
        'is_paid'           => 'boolean',
    ];

    /** @var array<int, string> */
    public $translatable = [
        'name',
        'description',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            if (! $model->starts_at || ! $model->ends_at) {
                $model->setNewPeriod();
            }

            // Ensure only one active subscription per subscriber
            if ($model->is_active && $model->subscriber_id && $model->subscriber_type) {
                $model->deactivateOtherSubscriptions();
            }
        });

        static::updating(function (self $model): void {
            // If this subscription is being activated, deactivate all other active subscriptions
            if ($model->isDirty('is_active') && $model->is_active && $model->subscriber_id && $model->subscriber_type) {
                $model->deactivateOtherSubscriptions();
            }
        });

        static::deleted(function (self $subscription): void {
            $subscription->usage()->delete();

            // Fire event for project-specific logic (cache clearing, etc.)
            event(new SubscriptionDeleted($subscription));
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function subscriber(): MorphTo
    {
        return $this->morphTo('subscriber', 'subscriber_type', 'subscriber_id', 'id');
    }

    /**
     * @return HasMany<PlanSubscriptionUsage, $this>
     */
    public function usage(): HasMany
    {
        return $this->hasMany(PlanSubscriptionUsage::class, 'subscription_id');
    }

    public function active(): bool
    {
        return $this->is_active && ! $this->trashed();
    }

    public function inactive(): bool
    {
        return ! $this->active();
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && Carbon::now()->lt($this->trial_ends_at);
    }

    public function canceled(): bool
    {
        return $this->canceled_at && Carbon::now()->gte($this->canceled_at);
    }

    public function ended(): bool
    {
        return $this->ends_at && Carbon::now()->gte($this->ends_at);
    }

    public function cancel(bool $immediately = false): self
    {
        $this->canceled_at = Carbon::now();
        $this->is_active   = false;

        if ($immediately) {
            $this->ends_at = $this->canceled_at;
        }

        $this->save();

        return $this;
    }

    public function changePlan(Plan $plan): self
    {
        // If plans does not have the same billing frequency
        // (e.g., invoice_interval and invoice_period) we will update
        // the billing dates starting today, and since we are basically creating
        // a new billing cycle, the usage data will be cleared.
        if ($this->plan->invoice_interval !== $plan->invoice_interval || $this->plan->invoice_period !== $plan->invoice_period) {
            $this->setNewPeriod($plan->invoice_interval, $plan->invoice_period);
            $this->usage()->delete();
        }

        // Attach new plan to subscription
        $this->plan_id = $plan->id;
        $this->save();

        return $this;
    }

    /**
     * Renew subscription period.
     *
     * @return $this
     *
     * @throws LogicException
     */
    public function renew(): self
    {
        if ($this->ended() && $this->canceled()) {
            throw new LogicException('Unable to renew canceled ended subscription.');
        }

        $subscription = $this;

        DB::transaction(function () use ($subscription): void {
            // Deactivate all other active subscriptions for this subscriber
            $subscription->deactivateOtherSubscriptions();

            // Clear usage data
            $subscription->usage()->delete();

            // Renew period
            $subscription->setNewPeriod();
            $subscription->canceled_at = null;
            $subscription->is_active   = true;
            $subscription->save();
        });

        return $this;
    }

    /**
     * Scope subscription by subscriber.
     *
     * @param  Builder<PlanSubscription>  $builder
     * @return Builder<PlanSubscription>
     */
    public function scopeOfSubscriber(Builder $builder, Model $subscriber): Builder
    {
        return $builder->where('subscriber_type', $subscriber->getMorphClass())
            ->where('subscriber_id', $subscriber->getKey());
    }

    /**
     * Scope subscriptions with ending trial.
     *
     * @param  Builder<PlanSubscription>  $builder
     * @return Builder<PlanSubscription>
     */
    public function scopeFindEndingTrial(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to   = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('trial_ends_at', [$from, $to]);
    }

    /**
     * Scope subscriptions with ended trial.
     *
     * @param  Builder<PlanSubscription>  $builder
     * @return Builder<PlanSubscription>
     */
    public function scopeFindEndedTrial(Builder $builder): Builder
    {
        return $builder->where('trial_ends_at', '<=', Carbon::now());
    }

    /**
     * Scope subscriptions with ending period.
     *
     * @param  Builder<PlanSubscription>  $builder
     * @return Builder<PlanSubscription>
     */
    public function scopeFindEndingPeriod(Builder $builder, int $dayRange = 3): Builder
    {
        $from = Carbon::now();
        $to   = Carbon::now()->addDays($dayRange);

        return $builder->whereBetween('ends_at', [$from, $to]);
    }

    /**
     * Scope subscriptions with ended period.
     *
     * @param  Builder<PlanSubscription>  $builder
     * @return Builder<PlanSubscription>
     */
    public function scopeFindEndedPeriod(Builder $builder): Builder
    {
        return $builder->where('ends_at', '<=', Carbon::now());
    }

    /**
     * @param  Builder<PlanSubscription>  $builder
     * @return Builder<PlanSubscription>
     */
    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('is_active', true);
    }

    /**
     * Scope active subscriptions.
     *
     * @param  Builder<PlanSubscription>  $builder
     * @return Builder<PlanSubscription>
     */
    public function scopeFindActive(Builder $builder): Builder
    {
        return $builder->active();
    }

    /**
     * Set new subscription period.
     *
     * @return $this
     */
    protected function setNewPeriod(Interval|string $invoice_interval = '', ?int $invoice_period = null, ?Carbon $start = null): self
    {
        if (empty($invoice_interval)) {
            $invoice_interval = $this->plan->invoice_interval;
        }
        // Ensure $invoice_interval is always a string
        if ($invoice_interval instanceof Interval) {
            $invoice_interval = $invoice_interval->value;
        }

        if (empty($invoice_period)) {
            $invoice_period = $this->plan->invoice_period;
        }

        $period = new Period(
            interval: $invoice_interval,
            count: $invoice_period,
            start: $start ?? Carbon::now()
        );

        $this->starts_at = $period->getStartDate();
        $this->ends_at   = $period->getEndDate();

        return $this;
    }

    public function recordFeatureUsage(string $featureCode, int $uses = 1, bool $incremental = true): PlanSubscriptionUsage
    {
        $feature = $this->plan->features()->where('code', $featureCode)->firstOrFail();

        $usage = $this->usage()->firstOrNew([
            'subscription_id' => $this->getKey(),
            'feature_id'      => $feature->getKey(),
        ]);

        if ($feature->resettable_period) {
            // Set expiration date when the usage record is new or doesn't have one.
            if ($usage->valid_until === null) {
                // Set date from subscription creation date so the reset
                // period match the period specified by the subscription's plan.
                $usage->valid_until = $feature->getResetDate($this->created_at);
            } elseif ($usage->expired()) {
                // If the usage record has been expired, let's assign
                // a new expiration date and reset the uses to zero.
                $usage->valid_until = $feature->getResetDate($usage->valid_until);
                $usage->used        = 0;
            }
        }

        $usage->used = $incremental ? $usage->used + $uses : $uses;

        $usage->save();

        return $usage;
    }

    public function reduceFeatureUsage(string $featureCode, int $uses = 1): ?PlanSubscriptionUsage
    {
        $usage = $this->usage()->byFeatureCode($featureCode, $this->plan_id)->first();

        if ($usage === null) {
            return null;
        }

        $usage->used = max($usage->used - $uses, 0);

        $usage->save();

        return $usage;
    }

    /**
     * Determine if the feature can be used.
     */
    public function canUseFeature(string $featureCode): bool
    {
        $featureValue = $this->getFeatureValue($featureCode);
        $usage        = $this->usage()->byFeatureCode($featureCode, $this->plan_id)->first();

        if ($featureValue === -1) {
            return true;
        }

        if ($featureValue === 0 || ! $usage || $usage->expired()) {
            return false;
        }

        return $this->getFeatureRemainings($featureCode) > 0;
    }

    /**
     * Get how many times the feature has been used.
     */
    public function getFeatureUsage(string $featureCode): int
    {
        $usage = $this->usage()->byFeatureCode($featureCode, $this->plan_id)->first();

        return (! $usage || $usage->expired()) ? 0 : (int) $usage->used;
    }

    /**
     * Get the available uses.
     */
    public function getFeatureRemainings(string $featureCode): int
    {
        return $this->getFeatureValue($featureCode) - $this->getFeatureUsage($featureCode);
    }

    public function getFeatureValue(string $featureCode): int
    {
        $feature = $this->plan->features()->where('code', $featureCode)->first();

        return (int) ($feature->value ?? 0);
    }

    /**
     * @return BelongsTo<Plan, $this>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    /**
     * Get remaining feature usage including additional purchased features.
     *
     * This method can be extended to include additional feature purchases.
     * Override getAdditionalFeatureQuantity() to add custom logic.
     */
    public function getFeatureRemaining(string $featureCode): int
    {
        $baseRemaining      = $this->getFeatureRemainings($featureCode);
        $additionalQuantity = $this->getAdditionalFeatureQuantity($featureCode);
        $companyUsage       = $this->getCompanyFeatureUsage($featureCode);

        return ($this->getFeatureValue($featureCode) + $additionalQuantity) - $companyUsage;
    }

    /**
     * Get the quantity of additional features purchased for this subscription/subscriber.
     *
     * Override this method in your extended model to add support for additional feature purchases.
     */
    protected function getAdditionalFeatureQuantity(string $featureCode): int
    {
        return $this->getFeatureAvailableQuantity($featureCode);
    }

    /**
     * Get the available quantity of a feature that was purchased and added to this subscription.
     *
     * This method queries the plan_subscription_features table to get the quantity
     * of additional features purchased for this subscription. It can be configured
     * via config('subscription-plans.models.plan_subscription_feature') to use a
     * custom model class, or it will use DB facade to query the table directly.
     *
     * @param  string  $featureCode  The code of the feature
     * @return int The quantity of the feature available (0 if not found)
     */
    public function getFeatureAvailableQuantity(string $featureCode): int
    {
        // Ensure plan relationship is loaded
        if (! $this->relationLoaded('plan')) {
            $this->load('plan');
        }

        if (! $this->plan) {
            return 0;
        }

        // Find the feature by code
        $feature = $this->plan->features()->where('code', $featureCode)->first();

        if (! $feature) {
            return 0;
        }

        // Try to use configured model class if available
        $modelClass = config('subscription-plans.models.plan_subscription_feature');

        if ($modelClass && class_exists($modelClass)) {
            $subscriptionFeature = $modelClass::where('subscription_id', $this->id)
                ->where('feature_id', $feature->id)
                ->first();

            return $subscriptionFeature ? (int) $subscriptionFeature->quantity : 0;
        }

        // Fallback to DB facade if model class is not configured
        $tableName = config('subscription-plans.table_names.plan_subscription_features', 'plan_subscription_features');

        $result = DB::table($tableName)
            ->where('subscription_id', $this->id)
            ->where('feature_id', $feature->id)
            ->whereNull('deleted_at')
            ->first();

        return $result ? (int) $result->quantity : 0;
    }

    /**
     * Get total feature usage across all subscriptions for the subscriber.
     *
     * Override this method to implement company-wide or subscriber-wide usage tracking.
     */
    protected function getCompanyFeatureUsage(string $featureCode): int
    {
        // Default: only consider this subscription's usage
        return $this->getFeatureUsage($featureCode);
    }

    public function decreaseUsage(string $featureCode, int $amount = 1): void
    {
        /** @var PlanFeature|null $feature */
        $feature = $this->plan->features()->where('code', $featureCode)->first();

        if (! $feature) {
            return;
        }

        /** @var PlanSubscriptionUsage|null $usage */
        $usage = $this->usage()->where('feature_id', $feature->id)->first();

        if ($usage) {
            $usage->used = max(0, ($usage->used - $amount));
            $usage->save();
        }
    }

    /**
     * Get total feature balance (plan value + any additional purchased features).
     *
     * This method includes the base plan feature value plus any additional
     * purchased features. Override getAdditionalFeatureQuantity() to add custom logic.
     */
    public function getTotalFeatureBalance(string $featureCode): int
    {
        return (int) $this->getFeatureValue($featureCode) + $this->getAdditionalFeatureQuantity($featureCode);
    }

    /**
     * Deactivate all other active subscriptions for the same subscriber.
     * This ensures only one subscription can be active at a time.
     */
    protected function deactivateOtherSubscriptions(): void
    {
        if (! $this->subscriber_id || ! $this->subscriber_type) {
            return;
        }

        $query = static::query()
            ->where('subscriber_type', $this->subscriber_type)
            ->where('subscriber_id', $this->subscriber_id)
            ->where('is_active', true);

        // Exclude current subscription if it has an ID (for updates)
        if ($this->getKey()) {
            $query->where('id', '!=', $this->getKey());
        }

        // Update each subscription individually to ensure events are fired
        // This is important for cache clearing and other event listeners
        $query->get()->each(function (self $subscription): void {
            $subscription->is_active = false;
            $subscription->save();
        });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PlanSubscriptionFactory
    {
        return PlanSubscriptionFactory::new();
    }
}
