<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Enums;


enum SubscriptionModel: string
{
    case Payg  = 'payg';
    case Fixed = 'fixed';

    /**
     * Get the display label for the subscription model.
     * Get the display label.
     */
    public function getLabel(): string
    {
        return __('subscription-plans::subscription-plans.subscription-model.'.$this->value);
    }
}
