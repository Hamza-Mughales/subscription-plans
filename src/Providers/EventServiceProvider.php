<?php

namespace NootPro\SubscriptionPlans\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use NootPro\SubscriptionPlans\Events\SubscriptionCreated;
use NootPro\SubscriptionPlans\Events\SubscriptionDeleted;
use NootPro\SubscriptionPlans\Events\SubscriptionRestored;
use NootPro\SubscriptionPlans\Events\SubscriptionUpdated;
use NootPro\SubscriptionPlans\Listeners\ClearSubscriptionCache;
use NootPro\SubscriptionPlans\Events\ModuleCreated;
use NootPro\SubscriptionPlans\Events\ModuleUpdated;
use NootPro\SubscriptionPlans\Events\ModuleDeleted;
use NootPro\SubscriptionPlans\Events\ModuleRestored;
use NootPro\SubscriptionPlans\Listeners\RefreshSubscriberModuleCacheOnModuleEvents;
use NootPro\SubscriptionPlans\Listeners\RefreshSubscriberModuleCacheOnSubscriptionEvents;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SubscriptionCreated::class => [ClearSubscriptionCache::class, RefreshSubscriberModuleCacheOnSubscriptionEvents::class],
        SubscriptionUpdated::class => [ClearSubscriptionCache::class, RefreshSubscriberModuleCacheOnSubscriptionEvents::class],
        SubscriptionDeleted::class => [ClearSubscriptionCache::class, RefreshSubscriberModuleCacheOnSubscriptionEvents::class],
        SubscriptionRestored::class => [ClearSubscriptionCache::class, RefreshSubscriberModuleCacheOnSubscriptionEvents::class],
        ModuleCreated::class => [RefreshSubscriberModuleCacheOnModuleEvents::class],
        ModuleUpdated::class => [RefreshSubscriberModuleCacheOnModuleEvents::class],
        ModuleDeleted::class => [RefreshSubscriberModuleCacheOnModuleEvents::class],
        ModuleRestored::class => [RefreshSubscriberModuleCacheOnModuleEvents::class],
    ];

    public function boot(): void
    {
        $enabled = (bool) config('subscription-plans.listeners.enabled', true);
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


