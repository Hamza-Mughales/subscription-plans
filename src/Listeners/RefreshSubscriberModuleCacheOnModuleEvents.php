<?php

namespace HamzaMughales\Subscriptions\Listeners;

class RefreshSubscriberModuleCacheOnModuleEvents
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $module = $event->module ?? null;
        $plan   = $module?->plan;

        if (! $plan) {
            return;
        }

        // Refresh cache for active subscriptions' subscribers
        $plan->subscriptions()
            ->with('subscriber')
            ->get()
            ->filter(function ($subscription) {
                return method_exists($subscription, 'active') ? $subscription->active() : true;
            })
            ->each(function ($subscription) {
                $subscriber = $subscription->subscriber;
                if ($subscriber) {
                    \HamzaMughales\Subscriptions\Facades\SubscriptionPlans::refreshModuleCache($subscriber);
                }
            });
    }
}
