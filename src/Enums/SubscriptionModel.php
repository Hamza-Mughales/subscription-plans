<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Enums;

use Filament\Support\Contracts\HasLabel;

enum SubscriptionModel: string implements HasLabel
{
    case Payg  = 'payg';
    case Fixed = 'fixed';

    /**
     * Get the display label for the subscription model.
     * Compatible with Filament's HasLabel interface.
     */
    public function getLabel(): string
    {
        return __('subscription-plans::subscription-plans.subscription-model.'.$this->value);
    }
}
