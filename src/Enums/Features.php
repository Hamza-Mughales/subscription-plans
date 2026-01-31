<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Enums;

use Filament\Support\Contracts\HasLabel;

enum Features: string implements HasLabel
{
    case Users = 'users';

    /**
     * Get the display label for the feature.
     * Compatible with Filament's HasLabel interface.
     */
    public function getLabel(): string
    {
        return __('subscription-plans::subscription-plans.features.'.$this->value);
    }
}
