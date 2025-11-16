<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Enums;

use Filament\Support\Contracts\HasLabel;

enum PlanType: string implements HasLabel
{
    case Plan = 'plan';
    case TestPlan = 'test_plan';

    /**
     * Get the display label for the plan type.
     * Compatible with Filament's HasLabel interface.
     */
    public function getLabel(): string
    {
        return __('subscription-plans::subscription-plans.plan-type.'.$this->value);
    }
}
