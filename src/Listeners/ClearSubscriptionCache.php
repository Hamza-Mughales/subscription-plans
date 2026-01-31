<?php

namespace HamzaMughales\Subscriptions\Listeners;

use HamzaMughales\Subscriptions\Facades\SubscriptionPlans;

class ClearSubscriptionCache
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $subscription = $event->subscription ?? null;

        if (! $subscription) {
            return;
        }

        // Ensure subscriber relationship is loaded
        $subscription->loadMissing('subscriber');
        $subscriber = $subscription->subscriber;

        if ($subscriber) {
            SubscriptionPlans::clearSubscriptionCache($subscriber);
        }
    }
}
