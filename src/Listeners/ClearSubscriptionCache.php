<?php

namespace NootPro\SubscriptionPlans\Listeners;

use NootPro\SubscriptionPlans\Facades\SubscriptionPlans;

class ClearSubscriptionCache
{
    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $subscription = $event->subscription ?? null;
        $subscriber   = $subscription?->subscriber;

        if ($subscriber) {
            SubscriptionPlans::clearSubscriptionCache($subscriber);
        }
    }
}
