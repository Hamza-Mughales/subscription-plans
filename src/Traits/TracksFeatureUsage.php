<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Traits;

use Illuminate\Database\Eloquent\Model;
use NootPro\SubscriptionPlans\Models\PlanSubscription;

/**
 * Trait for tracking subscription feature usage.
 *
 * This trait provides helper methods for observers and listeners
 * to easily track feature usage when models are created/deleted.
 *
 * @mixin HasPlanSubscriptions
 */
trait TracksFeatureUsage
{
    /**
     * Record feature usage for a subscriber.
     *
     * @param  Model|null  $subscriber  The subscriber model (must use HasPlanSubscriptions trait)
     * @param  string  $featureSlug  The feature slug to track
     * @param  int  $uses  Number of uses to record (default: 1)
     * @return bool  Returns true if usage was recorded, false otherwise
     */
    protected function recordFeatureUsageForSubscriber(?Model $subscriber, string $featureSlug, int $uses = 1): bool
    {
        if (! $subscriber) {
            return false;
        }

        // Ensure subscriber uses HasPlanSubscriptions trait
        if (! $this->hasSubscriptionsTrait($subscriber)) {
            return false;
        }

        $activeSubscription = $subscriber->activePlanSubscription();

        if (! $activeSubscription) {
            return false;
        }

        // Check if the subscription's plan has the feature
        if (! $this->planHasFeature($activeSubscription, $featureSlug)) {
            return false;
        }

        $activeSubscription->recordFeatureUsage($featureSlug, $uses);

        return true;
    }

    /**
     * Decrease feature usage for a subscriber.
     *
     * @param  Model|null  $subscriber  The subscriber model (must use HasPlanSubscriptions trait)
     * @param  string  $featureSlug  The feature slug to decrease
     * @param  int  $amount  Amount to decrease (default: 1)
     * @return bool  Returns true if usage was decreased, false otherwise
     */
    protected function decreaseFeatureUsageForSubscriber(?Model $subscriber, string $featureSlug, int $amount = 1): bool
    {
        if (! $subscriber) {
            return false;
        }

        // Ensure subscriber uses HasPlanSubscriptions trait
        if (! $this->hasSubscriptionsTrait($subscriber)) {
            return false;
        }

        $activeSubscription = $subscriber->activePlanSubscription();

        if (! $activeSubscription) {
            return false;
        }

        // Check if the subscription's plan has the feature
        if (! $this->planHasFeature($activeSubscription, $featureSlug)) {
            return false;
        }

        $activeSubscription->decreaseUsage($featureSlug, $amount);

        return true;
    }

    /**
     * Check if a plan subscription's plan has a specific feature.
     *
     * @param  PlanSubscription  $subscription  The subscription to check
     * @param  string  $featureSlug  The feature slug to check for
     * @return bool  Returns true if the plan has the feature, false otherwise
     */
    protected function planHasFeature(PlanSubscription $subscription, string $featureSlug): bool
    {
        $plan = $subscription->plan;

        if (! $plan) {
            return false;
        }

        return $plan->features()->where('slug', $featureSlug)->exists();
    }

    /**
     * Check if a model uses the HasPlanSubscriptions trait.
     *
     * @param  Model  $model  The model to check
     * @return bool  Returns true if the model uses HasPlanSubscriptions trait
     */
    protected function hasSubscriptionsTrait(Model $model): bool
    {
        return in_array(HasPlanSubscriptions::class, class_uses_recursive($model), true);
    }
}

