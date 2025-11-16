<?php

namespace NootPro\SubscriptionPlans\Listeners;

use Illuminate\Support\Facades\Cache;
use NootPro\SubscriptionPlans\Enums\Modules as PackageModulesEnum;

class RefreshSubscriberModuleCacheOnSubscriptionEvents
{
    public function handle($event): void
    {
        $subscription = $event->subscription ?? null;
        $subscriber = $subscription?->subscriber;

        if (! $subscription || ! $subscriber) {
            return;
        }

        // Determine caller event by class short name
        $eventClass = class_basename($event);

        if ($eventClass === 'SubscriptionCreated' || $eventClass === 'SubscriptionRestored') {
                \NootPro\SubscriptionPlans\Facades\SubscriptionPlans::refreshModuleCache($subscriber);
            return;
        }

        if ($eventClass === 'SubscriptionUpdated') {
                \NootPro\SubscriptionPlans\Facades\SubscriptionPlans::refreshModuleCache($subscriber);
            return;
        }

        if ($eventClass === 'SubscriptionDeleted') {
            \NootPro\SubscriptionPlans\Facades\SubscriptionPlans::clearModuleCache($subscriber);

            // Clear per-module caches if Modules enum exists/configured
            $modulesEnum = config('subscription-plans.enums.modules', PackageModulesEnum::class);
            if (enum_exists($modulesEnum)) {
                foreach ($modulesEnum::cases() as $module) {
                    Cache::forget("company_{$subscriber->id}_module_{$module->value}");
                }
            }
            return;
        }
    }
}


