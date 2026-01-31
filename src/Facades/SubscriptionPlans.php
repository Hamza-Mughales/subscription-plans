<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \HamzaMughales\Subscriptions\Models\Plan|null findPlan(string|int $identifier)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \HamzaMughales\Subscriptions\Models\Plan> getActivePlans()
 * @method static \Illuminate\Database\Eloquent\Collection<int, \HamzaMughales\Subscriptions\Models\Plan> getVisiblePlans()
 * @method static bool hasActiveSubscription(object $subscriber)
 * @method static \HamzaMughales\Subscriptions\Models\PlanSubscription|null getActiveSubscription(object $subscriber)
 * @method static void clearSubscriptionCache(object $subscriber)
 * @method static bool moduleEnabled(object $subscriber, string $module)
 * @method static void clearModuleCache(object $subscriber)
 * @method static void refreshModuleCache(object $subscriber)
 *
 * @see \HamzaMughales\Subscriptions\Services\SubscriptionPlansService
 */
class SubscriptionPlans extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'subscription-plans';
    }
}
