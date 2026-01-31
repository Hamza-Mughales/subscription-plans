<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Enums;

enum InvoiceTransactionStatus: string
{
    case Pending   = 'pending';
    case Completed = 'completed';
    case Failed    = 'failed';
    case Refunded  = 'refunded';

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Failed, self::Refunded], true);
    }

    /**
     * Get the translated label for the transaction status.
     */
    public function label(): string
    {
        return __('subscription-plans::subscription-plans.invoice_transaction_status.'.$this->value);
    }
}
