<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use HamzaMughales\Subscriptions\Traits\HasPlanSubscriptions;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to ensure the subscriber has an active subscription.
 *
 * This middleware checks if the authenticated user (or tenant) has an active subscription.
 * If not, it redirects to a configurable route or returns a 403 response.
 *
 * Usage:
 * - In routes: Route::middleware(['auth', 'subscription'])->group(...)
 * - In Kernel: 'subscription' => \HamzaMughales\Subscriptions\Http\Middleware\EnsureSubscriptionValid::class
 */
class EnsureSubscriptionValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $redirectTo = null): Response
    {
        $subscriber = $this->getSubscriber($request);

        if (! $subscriber) {
            return $this->handleNoSubscriber($request, $next);
        }

        // Check if subscriber has active subscription (cached for performance)
        $cacheKey              = $this->getCacheKey($subscriber);
        $cacheTtl              = config('subscription-plans.cache.ttl', config('subscription-plans.cache_ttl', 30));
        $hasActiveSubscription = Cache::remember(
            $cacheKey,
            now()->addMinutes($cacheTtl),
            fn () => $this->hasActiveSubscription($subscriber)
        );

        if (! $hasActiveSubscription) {
            return $this->handleNoSubscription($request, $redirectTo);
        }

        return $next($request);
    }

    /**
     * Get the subscriber from the request.
     *
     * This method attempts to get the subscriber from:
     * 1. Filament tenant (if using Filament)
     * 2. Authenticated user (if using standard auth)
     * 3. Custom resolver from config
     */
    protected function getSubscriber(Request $request): ?object
    {
        // Try Filament tenant first
        if (class_exists(\Filament\Facades\Filament::class)) {
            $tenant = \Filament\Facades\Filament::getTenant();
            if ($tenant && $this->hasTrait($tenant)) {
                return $tenant;
            }
        }

        // Try authenticated user
        $user = $request->user();
        if ($user && $this->hasTrait($user)) {
            return $user;
        }

        // Try custom resolver from config
        $resolver = config('subscription-plans.subscriber_resolver');
        if ($resolver && is_callable($resolver)) {
            $subscriber = $resolver($request);
            if ($subscriber && $this->hasTrait($subscriber)) {
                return $subscriber;
            }
        }

        return null;
    }

    /**
     * Check if the model has the HasPlanSubscriptions trait.
     */
    protected function hasTrait(object $model): bool
    {
        return in_array(HasPlanSubscriptions::class, class_uses_recursive($model), true);
    }

    /**
     * Check if subscriber has an active subscription.
     */
    protected function hasActiveSubscription(object $subscriber): bool
    {
        if (! method_exists($subscriber, 'activePlanSubscription')) {
            return false;
        }

        $subscription = $subscriber->activePlanSubscription();

        return $subscription !== null && $subscription->active();
    }

    /**
     * Get cache key for subscriber.
     */
    protected function getCacheKey(object $subscriber): string
    {
        $type = get_class($subscriber);
        $id   = $subscriber->getKey();

        return "subscription_status_{$type}_{$id}";
    }

    /**
     * Handle request when no subscriber is found.
     */
    protected function handleNoSubscriber(Request $request, Closure $next): Response
    {
        // If no subscriber found, allow request to continue
        // (might be a public route or handled by auth middleware)
        return $next($request);
    }

    /**
     * Handle request when subscriber has no active subscription.
     */
    protected function handleNoSubscription(Request $request, ?string $redirectTo = null): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => __('subscription-plans::subscription-plans.no_active_subscription'),
            ], 403);
        }

        if (class_exists(\Filament\Facades\Filament::class)) {
            $panel = \Filament\Facades\Filament::getPanel('subscription');
            if ($panel) {
                return redirect()->to((string) $panel->getUrl());
            }
        }

        return redirect()->route($redirectTo ?? config('subscription-plans.middleware.redirect_route', 'subscription.plans'));
    }
}
