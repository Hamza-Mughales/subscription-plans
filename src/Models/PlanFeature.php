<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use InvalidArgumentException;
use NootPro\SubscriptionPlans\Enums\Features;
use NootPro\SubscriptionPlans\Services\Period;
use NootPro\SubscriptionPlans\Traits\BelongsToPlan;
use NootPro\SubscriptionPlans\Traits\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

/**
 * PlanFeature.
 *
 * @property-read Plan $plan
 * @property-read Collection|PlanSubscriptionUsage[] $usage
 *
 * @method static Builder|PlanFeature byPlanId($planId)
 * @method static Builder|PlanFeature whereCreatedAt($value)
 * @method static Builder|PlanFeature whereDeletedAt($value)
 * @method static Builder|PlanFeature whereDescription($value)
 * @method static Builder|PlanFeature whereId($value)
 * @method static Builder|PlanFeature whereTitle($value)
 * @method static Builder|PlanFeature wherePlanId($value)
 * @method static Builder|PlanFeature whereResettableInterval($value)
 * @method static Builder|PlanFeature whereResettablePeriod($value)
 * @method static Builder|PlanFeature whereSlug($value)
 * @method static Builder|PlanFeature whereSortOrder($value)
 * @method static Builder|PlanFeature whereUpdatedAt($value)
 * @method static Builder|PlanFeature whereValue($value)
 */
class PlanFeature extends Model
{
    use BelongsToPlan;
    use HasSlug;
    use HasTranslations;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'plan_id',
        'slug',
        'name',
        'description',
        'value',
        'resettable_period',
        'resettable_interval',
        'sort_order',
    ];

    /** @var array<int, string> */
    public $translatable = [
        'name',
        'description',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'value'             => 'integer',
        'resettable_period' => 'integer',
        'sort_order'        => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::deleted(function (self $feature): void {
            $feature->usages()->delete();
        });

        static::creating(function (PlanFeature $feature) {
            if (static::where('plan_id', $feature->plan_id)->where('slug', $feature->slug)->exists()) {
                throw new InvalidArgumentException('Each plan should only have one feature with the same slug');
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->allowDuplicateSlugs()
            ->saveSlugsTo('slug');
    }

    /**
     * Ensure slug is always stored as a string
     */
    public function setSlugAttribute($value): void
    {
        if ($value instanceof Features || $value instanceof \BackedEnum) {
            $this->attributes['slug'] = $value->value;
        } else {
            $this->attributes['slug'] = $value;
        }
    }

    /**
     * @return HasMany<PlanSubscriptionUsage, $this>
     */
    public function usages(): HasMany
    {
        return $this->hasMany(PlanSubscriptionUsage::class);
    }

    public function getResetDate(?Carbon $dateFrom = null): Carbon
    {
        $period = new Period($this->resettable_interval, $this->resettable_period, $dateFrom ?? Carbon::now());

        return $period->getEndDate();
    }
}
