<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Events;

use Illuminate\Foundation\Events\Dispatchable;
use NootPro\SubscriptionPlans\Models\PlanModule;

class ModuleCreated
{
    use Dispatchable;

    public function __construct(
        public PlanModule $module
    ) {}
}
