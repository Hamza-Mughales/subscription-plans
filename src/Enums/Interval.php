<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Enums;

use Filament\Support\Contracts\HasLabel;

enum Interval: string implements HasLabel
{
    case Day   = 'day';
    case Week  = 'week';
    case Month = 'month';
    case Year  = 'year';

    /**
     * Get the display label for the interval.
     * Compatible with Filament's HasLabel interface.
     */
    public function getLabel(): string
    {
        return __('subscription-plans::subscription-plans.interval.'.$this->value);
    }
}
