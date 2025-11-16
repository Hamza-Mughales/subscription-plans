<?php

declare(strict_types=1);

use NootPro\SubscriptionPlans\Facades\SubscriptionPlans;
use NootPro\SubscriptionPlans\Models\Plan;
use NootPro\SubscriptionPlans\Models\PlanSubscription;

if (! function_exists('subscription_plan')) {
    /**
     * Get a plan by slug or ID.
     *
     * @param  string|int  $identifier
     * @return \NootPro\SubscriptionPlans\Models\Plan|null
     */
    function subscription_plan(string|int $identifier): ?Plan
    {
        return SubscriptionPlans::findPlan($identifier);
    }
}

if (! function_exists('has_active_subscription')) {
    /**
     * Check if subscriber has an active subscription.
     *
     * @param  object  $subscriber
     * @return bool
     */
    function has_active_subscription(object $subscriber): bool
    {
        return SubscriptionPlans::hasActiveSubscription($subscriber);
    }
}

if (! function_exists('active_subscription')) {
    /**
     * Get active subscription for subscriber.
     *
     * @param  object  $subscriber
     * @return \NootPro\SubscriptionPlans\Models\PlanSubscription|null
     */
    function active_subscription(object $subscriber): ?PlanSubscription
    {
        return SubscriptionPlans::getActiveSubscription($subscriber);
    }
}

if (! function_exists('clear_subscription_cache')) {
    /**
     * Clear subscription cache for subscriber.
     *
     * @param  object  $subscriber
     * @return void
     */
    function clear_subscription_cache(object $subscriber): void
    {
        SubscriptionPlans::clearSubscriptionCache($subscriber);
    }
}

