<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Traits;

use Illuminate\Database\Eloquent\Model;
use HamzaMughales\Subscriptions\Models\PlanSubscription;

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
     * @param  string  $featureCode  The feature code to track
     * @param  int  $uses  Number of uses to record (default: 1)
     * @return bool Returns true if usage was recorded, false otherwise
     */
    protected function recordFeatureUsageForSubscriber(?Model $subscriber, string $featureCode, int $uses = 1): bool
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
        if (! $this->planHasFeature($activeSubscription, $featureCode)) {
            return false;
        }

        $activeSubscription->recordFeatureUsage($featureCode, $uses);

        return true;
    }

    /**
     * Decrease feature usage for a subscriber.
     *
     * @param  Model|null  $subscriber  The subscriber model (must use HasPlanSubscriptions trait)
     * @param  string  $featureCode  The feature code to decrease
     * @param  int  $amount  Amount to decrease (default: 1)
     * @return bool Returns true if usage was decreased, false otherwise
     */
    protected function decreaseFeatureUsageForSubscriber(?Model $subscriber, string $featureCode, int $amount = 1): bool
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
        if (! $this->planHasFeature($activeSubscription, $featureCode)) {
            return false;
        }

        $activeSubscription->decreaseUsage($featureCode, $amount);

        return true;
    }

    /**
     * Check if a plan subscription's plan has a specific feature.
     *
     * @param  PlanSubscription  $subscription  The subscription to check
     * @param  string  $featureCode  The feature code to check for
     * @return bool Returns true if the plan has the feature, false otherwise
     */
    protected function planHasFeature(PlanSubscription $subscription, string $featureCode): bool
    {
        $plan = $subscription->plan;

        if (! $plan) {
            return false;
        }

        return $plan->features()->where('code', $featureCode)->exists();
    }

    /**
     * Check if a model uses the HasPlanSubscriptions trait.
     *
     * @param  Model  $model  The model to check
     * @return bool Returns true if the model uses HasPlanSubscriptions trait
     */
    protected function hasSubscriptionsTrait(Model $model): bool
    {
        return in_array(HasPlanSubscriptions::class, class_uses_recursive($model), true);
    }
}
