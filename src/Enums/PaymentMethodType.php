<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Enums;


enum PaymentMethodType: string
{
    case BankTransfer  = 'bank_transfer';
    case OnlinePayment = 'online_payment';
    case Visa          = 'visa';

    /**
     * Get all payment method type values as array.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the display label for the payment method type.
     * Get the display label.
     */
    public function getLabel(): string
    {
        return __('subscription-plans::subscription-plans.payment-method-type.'.$this->value);
    }
}
