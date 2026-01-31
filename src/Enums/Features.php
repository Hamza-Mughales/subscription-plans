<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Enums;

enum Features: string
{
    case Users = 'users';

    /**
     * Get the display label for the feature.
     */
    public function getLabel(): string
    {
        return __('subscription-plans::subscription-plans.features.'.$this->value);
    }
}
