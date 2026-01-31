<?php

namespace HamzaMughales\Subscriptions\Listeners;

class RefreshSubscriberModuleCacheOnSubscriptionEvents
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

        if (! $subscriber) {
            return;
        }

        // Determine caller event by class short name
        $eventClass = class_basename($event);

        // For all subscription events, clear the cache but don't refresh it immediately
        // This ensures cache is cleared, and it will be repopulated on next request
        // when the transaction has definitely committed and database changes are visible
        if ($eventClass === 'SubscriptionCreated' || $eventClass === 'SubscriptionRestored' || $eventClass === 'SubscriptionUpdated') {
            // Clear both subscription and module caches
            \HamzaMughales\Subscriptions\Facades\SubscriptionPlans::clearSubscriptionCache($subscriber);
            \HamzaMughales\Subscriptions\Facades\SubscriptionPlans::clearModuleCache($subscriber);

            return;
        }

        if ($eventClass === 'SubscriptionDeleted') {
            \HamzaMughales\Subscriptions\Facades\SubscriptionPlans::clearModuleCache($subscriber);
            \HamzaMughales\Subscriptions\Facades\SubscriptionPlans::clearSubscriptionCache($subscriber);

            return;
        }
    }
}
