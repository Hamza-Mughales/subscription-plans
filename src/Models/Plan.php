<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use NootPro\SubscriptionPlans\Database\Factories\PlanFactory;
use NootPro\SubscriptionPlans\Enums\Interval;
use NootPro\SubscriptionPlans\Enums\PlanType;
use NootPro\SubscriptionPlans\Enums\SubscriptionModel;
use NootPro\SubscriptionPlans\Traits\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

/**
 * Plan.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property float $price
 * @property Interval $invoice_interval
 * @property int $invoice_period
 * @property SubscriptionModel $subscription_model
 * @property int $sequence
 * @property-read Collection|PlanFeature[]      $features
 * @property-read Collection|PlanSubscription[] $subscriptions
 *
 * @method static Builder|Plan whereActiveSubscribersLimit($value)
 * @method static Builder|Plan whereCreatedAt($value)
 * @method static Builder|Plan whereCurrency($value)
 * @method static Builder|Plan whereDeletedAt($value)
 * @method static Builder|Plan whereDescription($value)
 * @method static Builder|Plan whereGraceInterval($value)
 * @method static Builder|Plan whereGracePeriod($value)
 * @method static Builder|Plan whereId($value)
 * @method static Builder|Plan whereInvoiceInterval($value)
 * @method static Builder|Plan whereInvoicePeriod($value)
 * @method static Builder|Plan whereIsActive($value)
 * @method static Builder|Plan whereName($value)
 * @method static Builder|Plan wherePrice($value)
 * @method static Builder|Plan whereProrateDay($value)
 * @method static Builder|Plan whereProrateExtendDue($value)
 * @method static Builder|Plan whereProratePeriod($value)
 * @method static Builder|Plan whereSignupFee($value)
 * @method static Builder|Plan whereSlug($value)
 * @method static Builder|Plan whereSequence($value)
 * @method static Builder|Plan whereSortOrder($value)
 * @method static Builder|Plan whereSubscriptionModel($value)
 * @method static Builder|Plan whereTrialInterval($value)
 * @method static Builder|Plan whereTrialPeriod($value)
 * @method static Builder|Plan whereUpdatedAt($value)
 */
class Plan extends Model
{
    /** @use HasFactory<PlanFactory> */
    use HasFactory;

    use HasSlug;
    use HasTranslations;
    use SoftDeletes;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('subscription-plans.table_names.plans', 'plan_plans');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'slug',
        'type',
        'name',
        'description',
        'is_active',
        'price',
        'signup_fee',
        'subscription_model',
        'currency',
        'trial_period',
        'trial_interval',
        'invoice_period',
        'invoice_interval',
        'grace_period',
        'grace_interval',
        'prorate_day',
        'prorate_period',
        'prorate_extend_due',
        'active_subscribers_limit',
        'sort_order',
        'is_visible',
        'sequence',
        'created_by',
        'updated_by',
    ];

    /** @var array<int, string> */
    public $translatable = [
        'name',
        'description',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'type'               => PlanType::class,
        'subscription_model' => SubscriptionModel::class,
        'invoice_interval'   => Interval::class,
        'trial_interval'     => Interval::class,
        'grace_interval'     => Interval::class,
        'is_active'          => 'boolean',
        'is_visible'         => 'boolean',
        'price'              => 'decimal:2',
        'signup_fee'         => 'decimal:2',
        'trial_period'       => 'integer',
        'invoice_period'     => 'integer',
        'grace_period'       => 'integer',
        'sort_order'         => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function (self $plan): void {
            DB::transaction(function () use ($plan) {
                $plan->features()->delete();
                $plan->subscriptions()->delete();
            });
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->allowDuplicateSlugs();
    }

    /**
     * @return HasMany<PlanFeature, $this>
     */
    public function features(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    /**
     * @return HasMany<PlanSubscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(PlanSubscription::class);
    }

    public function isFree(): bool
    {
        return $this->price <= 0.00;
    }

    public function hasTrial(): bool
    {
        return ((int) ($this->trial_period ?? 0)) > 0;
    }

    public function hasGrace(): bool
    {
        return ((int) ($this->grace_period ?? 0)) > 0;
    }

    public function getFeatureBySlug(string $featureSlug): ?PlanFeature
    {
        return $this->features()->where('code', $featureSlug)->first();
    }

    public function activate(): self
    {
        $this->update(['is_active' => true]);

        return $this;
    }

    public function deactivate(): self
    {
        $this->update(['is_active' => false]);

        return $this;
    }

    /**
     * @return HasMany<PlanModule, $this>
     */
    public function modules(): HasMany
    {
        return $this->hasMany(PlanModule::class);
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): PlanFactory
    {
        return PlanFactory::new();
    }
}
