<?php

declare(strict_types=1);

use HamzaMughales\Subscriptions\Facades\SubscriptionPlans;
use HamzaMughales\Subscriptions\Models\Plan;
use HamzaMughales\Subscriptions\Models\PlanSubscription;

if (! function_exists('subscription_plan')) {
    /**
     * Get a plan by slug or ID.
     */
    function subscription_plan(string|int $identifier): ?Plan
    {
        return SubscriptionPlans::findPlan($identifier);
    }
}

if (! function_exists('has_active_subscription')) {
    /**
     * Check if subscriber has an active subscription.
     */
    function has_active_subscription(object $subscriber): bool
    {
        return SubscriptionPlans::hasActiveSubscription($subscriber);
    }
}

if (! function_exists('active_subscription')) {
    /**
     * Get active subscription for subscriber.
     */
    function active_subscription(object $subscriber): ?PlanSubscription
    {
        return SubscriptionPlans::getActiveSubscription($subscriber);
    }
}

if (! function_exists('clear_subscription_cache')) {
    /**
     * Clear subscription cache for subscriber.
     */
    function clear_subscription_cache(object $subscriber): void
    {
        SubscriptionPlans::clearSubscriptionCache($subscriber);
    }
}
