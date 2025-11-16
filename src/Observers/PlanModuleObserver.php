<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Observers;

use NootPro\SubscriptionPlans\Events\ModuleCreated;
use NootPro\SubscriptionPlans\Events\ModuleDeleted;
use NootPro\SubscriptionPlans\Events\ModuleRestored;
use NootPro\SubscriptionPlans\Events\ModuleUpdated;
use NootPro\SubscriptionPlans\Models\PlanModule;

/**
 * PlanModuleObserver
 *
 * Observer for PlanModule model lifecycle events.
 * Fires events that projects can listen to for custom logic (cache clearing, etc.).
 */
class PlanModuleObserver
{
    /**
     * Handle the module "created" event.
     */
    public function created(PlanModule $module): void
    {
        event(new ModuleCreated($module));
    }

    /**
     * Handle the module "updated" event.
     */
    public function updated(PlanModule $module): void
    {
        event(new ModuleUpdated($module));
    }

    /**
     * Handle the module "deleted" event.
     */
    public function deleted(PlanModule $module): void
    {
        event(new ModuleDeleted($module));
    }

    /**
     * Handle the module "restored" event.
     */
    public function restored(PlanModule $module): void
    {
        event(new ModuleRestored($module));
    }
}
