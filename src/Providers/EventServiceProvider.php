<?php

namespace HamzaMughales\Subscriptions\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use HamzaMughales\Subscriptions\Events\ModuleCreated;
use HamzaMughales\Subscriptions\Events\ModuleDeleted;
use HamzaMughales\Subscriptions\Events\ModuleRestored;
use HamzaMughales\Subscriptions\Events\ModuleUpdated;
use HamzaMughales\Subscriptions\Events\SubscriptionCreated;
use HamzaMughales\Subscriptions\Events\SubscriptionDeleted;
use HamzaMughales\Subscriptions\Events\SubscriptionRestored;
use HamzaMughales\Subscriptions\Events\SubscriptionUpdated;
use HamzaMughales\Subscriptions\Listeners\ClearSubscriptionCache;
use HamzaMughales\Subscriptions\Listeners\RefreshSubscriberModuleCacheOnModuleEvents;
use HamzaMughales\Subscriptions\Listeners\RefreshSubscriberModuleCacheOnSubscriptionEvents;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SubscriptionCreated::class  => [ClearSubscriptionCache::class, RefreshSubscriberModuleCacheOnSubscriptionEvents::class],
        SubscriptionUpdated::class  => [ClearSubscriptionCache::class, RefreshSubscriberModuleCacheOnSubscriptionEvents::class],
        SubscriptionDeleted::class  => [ClearSubscriptionCache::class, RefreshSubscriberModuleCacheOnSubscriptionEvents::class],
        SubscriptionRestored::class => [ClearSubscriptionCache::class, RefreshSubscriberModuleCacheOnSubscriptionEvents::class],
        ModuleCreated::class        => [RefreshSubscriberModuleCacheOnModuleEvents::class],
        ModuleUpdated::class        => [RefreshSubscriberModuleCacheOnModuleEvents::class],
        ModuleDeleted::class        => [RefreshSubscriberModuleCacheOnModuleEvents::class],
        ModuleRestored::class       => [RefreshSubscriberModuleCacheOnModuleEvents::class],
    ];

    public function boot(): void
    {
        $enabled    = (bool) config('subscription-plans.listeners.enabled', true);
        $additional = (array) config('subscription-plans.listeners.additional', []);

        if (! $enabled) {
            $this->listen = [];
        }

        // Merge additional listeners: event => array of listeners
        foreach ($additional as $event => $listeners) {
            if (! is_array($listeners)) {
                $listeners = [$listeners];
            }
            $this->listen[$event] = array_values(array_unique(array_merge($this->listen[$event] ?? [], $listeners)));
        }

        parent::boot();
    }
}
