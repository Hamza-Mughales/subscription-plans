<?php

namespace NootPro\SubscriptionPlans\Listeners;

use NootPro\SubscriptionPlans\Facades\SubscriptionPlans;

class ClearSubscriptionCache
{
    public function handle($event): void
    {
        $subscription = $event->subscription ?? null;
        $subscriber   = $subscription?->subscriber;

        if ($subscriber) {
            SubscriptionPlans::clearSubscriptionCache($subscriber);
        }
    }
}
