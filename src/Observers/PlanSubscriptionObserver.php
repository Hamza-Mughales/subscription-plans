<?php

namespace NootPro\SubscriptionPlans\Observers;

use NootPro\SubscriptionPlans\Events\SubscriptionCreated;
use NootPro\SubscriptionPlans\Events\SubscriptionDeleted;
use NootPro\SubscriptionPlans\Events\SubscriptionRestored;
use NootPro\SubscriptionPlans\Events\SubscriptionUpdated;
use NootPro\SubscriptionPlans\Models\PlanSubscription;

/**
 * PlanSubscriptionObserver
 *
 * This observer fires events for subscription lifecycle.
 * Projects can listen to these events to handle their own logic
 * (cache clearing, notifications, etc.).
 */
class PlanSubscriptionObserver
{
    /**
     * Handle the subscription "created" event.
     */
    public function created(PlanSubscription $subscription): void
    {
        // Fire event for project-specific logic
        event(new SubscriptionCreated($subscription));
    }

    /**
     * Handle the subscription "updated" event.
     */
    public function updated(PlanSubscription $subscription): void
    {
        // Fire event for project-specific logic
        event(new SubscriptionUpdated($subscription));
    }

    /**
     * Handle the subscription "deleted" event.
     */
    public function deleted(PlanSubscription $subscription): void
    {
        // Fire event for project-specific logic
        // Note: Usage deletion is handled in PlanSubscription::boot()
        event(new SubscriptionDeleted($subscription));
    }

    /**
     * Handle the subscription "restored" event.
     */
    public function restored(PlanSubscription $subscription): void
    {
        // Fire event for project-specific logic
        event(new SubscriptionRestored($subscription));
    }
}
