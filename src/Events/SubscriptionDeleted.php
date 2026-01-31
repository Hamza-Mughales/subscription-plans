<?php

namespace HamzaMughales\Subscriptions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use HamzaMughales\Subscriptions\Models\PlanSubscription;

class SubscriptionDeleted
{
    use Dispatchable;

    public function __construct(
        public PlanSubscription $subscription
    ) {}
}
