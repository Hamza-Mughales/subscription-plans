<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use NootPro\SubscriptionPlans\Models\Plan;
use NootPro\SubscriptionPlans\Models\PlanSubscription;
use NootPro\SubscriptionPlans\Traits\HasPlanSubscriptions;

/**
 * Service class for subscription plans operations.
 */
class SubscriptionPlansService
{
    /**
     * Find a plan by slug or ID.
     */
    public function findPlan(string|int $identifier): ?Plan
    {
        if (is_numeric($identifier)) {
            return Plan::find($identifier);
        }

        return Plan::where('slug', $identifier)->first();
    }

    /**
     * Get all active plans.
     *
     * @return Collection<int, Plan>
     */
    public function getActivePlans(): Collection
    {
        return Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get all visible plans.
     *
     * @return Collection<int, Plan>
     */
    public function getVisiblePlans(): Collection
    {
        return Plan::where('is_active', true)
            ->where('is_visible', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Check if subscriber has an active subscription.
     */
    public function hasActiveSubscription(object $subscriber): bool
    {
        if (! $this->hasTrait($subscriber)) {
            return false;
        }

        return $this->getActiveSubscription($subscriber) !== null;
    }

    /**
     * Get active subscription for subscriber.
     */
    public function getActiveSubscription(object $subscriber): ?PlanSubscription
    {
        if (! $this->hasTrait($subscriber)) {
            return null;
        }

        $cacheKey = $this->getCacheKey($subscriber, 'active_subscription');

        $cacheTtl = config('subscription-plans.cache.ttl', config('subscription-plans.cache_ttl', 30));

        return Cache::remember(
            $cacheKey,
            now()->addMinutes($cacheTtl),
            function () use ($subscriber) {
                if (! method_exists($subscriber, 'activePlanSubscription')) {
                    return null;
                }

                return $subscriber->activePlanSubscription();
            }
        );
    }

    /**
     * Clear subscription cache for subscriber.
     */
    public function clearSubscriptionCache(object $subscriber): void
    {
        $type = get_class($subscriber);
        $id   = $subscriber->getKey();

        // Clear status cache (used by EnsureSubscriptionValid middleware)
        Cache::forget("subscription_status_{$type}_{$id}");

        // Clear active subscription cache (used by getActiveSubscription)
        Cache::forget($this->getCacheKey($subscriber, 'active_subscription'));
    }

    /**
     * Check if a module is enabled for the subscriber's active plan.
     */
    public function moduleEnabled(object $subscriber, string $module): bool
    {
        $type     = get_class($subscriber);
        $id       = $subscriber->getKey();
        $cacheKey = "module_enabled_{$type}_{$id}_{$module}";
        $cacheTtl = config('subscription-plans.cache.ttl', config('subscription-plans.cache_ttl', 30));

        return Cache::remember(
            $cacheKey,
            now()->addMinutes($cacheTtl),
            function () use ($subscriber, $module): bool {
                $activeSubscription = $this->getActiveSubscription($subscriber);
                if (! $activeSubscription || ! $activeSubscription->plan) {
                    return false;
                }

                return $activeSubscription->plan
                    ->modules()
                    ->where('module', $module)
                    ->exists();
            }
        );
    }

    /**
     * Clear all module caches for subscriber.
     */
    public function clearModuleCache(object $subscriber): void
    {
        $modulesEnum = config('subscription-plans.enums.modules');
        if (! $modulesEnum || ! enum_exists($modulesEnum)) {
            return;
        }
        $type = get_class($subscriber);
        $id   = $subscriber->getKey();
        foreach ($modulesEnum::cases() as $module) {
            $moduleValue = $module instanceof \BackedEnum ? $module->value : $module->name;
            Cache::forget("module_enabled_{$type}_{$id}_{$moduleValue}");
        }
    }

    /**
     * Refresh module cache entries for subscriber by recomputing each module.
     */
    public function refreshModuleCache(object $subscriber): void
    {
        // Clear active subscription cache first
        $this->clearSubscriptionCache($subscriber);

        $this->clearModuleCache($subscriber);
        $modulesEnum = config('subscription-plans.enums.modules');
        if (! $modulesEnum || ! enum_exists($modulesEnum)) {
            return;
        }
        foreach ($modulesEnum::cases() as $module) {
            // Warm the cache
            $moduleValue = $module instanceof \BackedEnum ? $module->value : $module->name;
            $this->moduleEnabled($subscriber, $moduleValue);
        }
    }

    /**
     * Check if model has HasPlanSubscriptions trait.
     */
    protected function hasTrait(object $model): bool
    {
        return in_array(HasPlanSubscriptions::class, class_uses_recursive($model), true);
    }

    /**
     * Get cache key for subscriber.
     */
    protected function getCacheKey(object $subscriber, string $suffix): string
    {
        $type = get_class($subscriber);
        $id   = $subscriber->getKey();

        return "subscription_{$suffix}_{$type}_{$id}";
    }
}
