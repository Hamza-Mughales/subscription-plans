<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Services;

use Illuminate\Database\Eloquent\Model;
use HamzaMughales\Subscriptions\Models\PlanSubscription;
use HamzaMughales\Subscriptions\Traits\HasPlanSubscriptions;

/**
 * Service class for managing subscription feature usage and limits.
 *
 * This class provides a generic way to check if a subscriber can use a feature
 * based on their subscription plan limits. It can be extended or configured
 * for project-specific needs.
 */
class SubscriptionFeatureManager
{
    /**
     * Callback to resolve the current subscriber/tenant.
     * Should return a model that uses HasPlanSubscriptions trait, or null.
     *
     * @var callable|null
     */
    protected static $subscriberResolver = null;

    /**
     * Callbacks for counting feature usage per subscriber.
     * Key is the feature code, value is a callable that receives the subscriber and returns the count.
     *
     * @var array<string, callable>
     */
    protected static array $featureCounters = [];

    /**
     * Set the subscriber resolver callback.
     *
     * @param  callable  $resolver  Callback that returns the current subscriber model or null
     */
    public static function setSubscriberResolver(callable $resolver): void
    {
        static::$subscriberResolver = $resolver;
    }

    /**
     * Register a feature counter callback.
     *
     * @param  string  $feature  Feature code
     * @param  callable  $counter  Callback that receives the subscriber and returns the count
     */
    public static function registerFeatureCounter(string $feature, callable $counter): void
    {
        static::$featureCounters[$feature] = $counter;
    }

    /**
     * Check if the current subscriber can use a feature based on plan limits.
     *
     * @param  string  $feature  Feature code
     * @param  Model|null  $subscriber  Optional subscriber model. If not provided, uses resolver.
     */
    public static function canUse(string $feature, ?Model $subscriber = null): bool
    {
        // Resolve subscriber if not provided
        if ($subscriber === null) {
            $subscriber = static::resolveSubscriber();
        }

        if ($subscriber === null) {
            return false;
        }

        // Ensure subscriber uses HasPlanSubscriptions trait
        if (! static::hasTrait($subscriber)) {
            return false;
        }

        // Get active subscription
        $activeSubscription = static::getActiveSubscription($subscriber);
        if ($activeSubscription === null) {
            return false;
        }

        // If a custom counter is registered, check against total balance
        if (isset(static::$featureCounters[$feature])) {
            $recordsCount = call_user_func(static::$featureCounters[$feature], $subscriber);

            return $activeSubscription->getTotalFeatureBalance($feature) > $recordsCount;
        }

        // Otherwise, check remaining usage (which already accounts for everything)
        return $activeSubscription->getFeatureRemaining($feature) > 0;
    }

    /**
     * Record usage of a feature.
     *
     * @param  string  $featureCode  Feature code
     * @param  Model|null  $subscriber  Optional subscriber model. If not provided, uses resolver.
     * @param  int  $uses  Number of uses to record (default: 1)
     */
    public static function recordUsage(string $featureCode, ?Model $subscriber = null, int $uses = 1): void
    {
        // Resolve subscriber if not provided
        if ($subscriber === null) {
            $subscriber = static::resolveSubscriber();
        }

        if ($subscriber === null) {
            return;
        }

        // Ensure subscriber uses HasPlanSubscriptions trait
        if (! static::hasTrait($subscriber)) {
            return;
        }

        $activeSubscription = static::getActiveSubscription($subscriber);
        if ($activeSubscription !== null) {
            $activeSubscription->recordFeatureUsage($featureCode, $uses);
        }
    }

    /**
     * Resolve the current subscriber using the configured resolver.
     */
    protected static function resolveSubscriber(): ?Model
    {
        if (static::$subscriberResolver === null) {
            return null;
        }

        $subscriber = call_user_func(static::$subscriberResolver);

        return $subscriber instanceof Model ? $subscriber : null;
    }

    /**
     * Get active subscription for subscriber.
     *
     * @param  Model  $subscriber  Subscriber model
     */
    protected static function getActiveSubscription(Model $subscriber): ?PlanSubscription
    {
        if (! method_exists($subscriber, 'activePlanSubscription')) {
            return null;
        }

        return $subscriber->activePlanSubscription();
    }

    /**
     * Check if model has HasPlanSubscriptions trait.
     */
    protected static function hasTrait(object $model): bool
    {
        return in_array(HasPlanSubscriptions::class, class_uses_recursive($model), true);
    }
}
