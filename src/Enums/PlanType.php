<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Enums;


enum PlanType: string
{
    case Plan     = 'plan';
    case TestPlan = 'test_plan';

    /**
     * Get the display label for the plan type.
     * Get the display label.
     */
    public function getLabel(): string
    {
        return __('subscription-plans::subscription-plans.plan-type.'.$this->value);
    }
}
