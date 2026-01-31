<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Events;

use Illuminate\Foundation\Events\Dispatchable;
use HamzaMughales\Subscriptions\Models\PlanModule;

class ModuleDeleted
{
    use Dispatchable;

    public function __construct(
        public PlanModule $module
    ) {}
}
