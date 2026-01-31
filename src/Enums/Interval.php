<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Enums;


enum Interval: string
{
    case Hour  = 'hour';
    case Day   = 'day';
    case Week  = 'week';
    case Month = 'month';
    case Year  = 'year';

    /**
     * Get the display label for the interval.
     * Get the display label.
     */
    public function getLabel(): string
    {
        return __('subscription-plans::subscription-plans.interval.'.$this->value);
    }
}
