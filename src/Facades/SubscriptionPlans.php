<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \NootPro\SubscriptionPlans\Models\Plan|null findPlan(string|int $identifier)
 * @method static \Illuminate\Database\Eloquent\Collection<int, \NootPro\SubscriptionPlans\Models\Plan> getActivePlans()
 * @method static \Illuminate\Database\Eloquent\Collection<int, \NootPro\SubscriptionPlans\Models\Plan> getVisiblePlans()
 * @method static bool hasActiveSubscription(object $subscriber)
 * @method static \NootPro\SubscriptionPlans\Models\PlanSubscription|null getActiveSubscription(object $subscriber)
 * @method static void clearSubscriptionCache(object $subscriber)
 * @method static bool moduleEnabled(object $subscriber, string $module)
 * @method static void clearModuleCache(object $subscriber)
 * @method static void refreshModuleCache(object $subscriber)
 *
 * @see \NootPro\SubscriptionPlans\Services\SubscriptionPlansService
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
