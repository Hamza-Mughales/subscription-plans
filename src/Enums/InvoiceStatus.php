<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Enums;

enum InvoiceStatus: string
{
    case New           = 'new';
    case Pending       = 'pending';
    case Paid          = 'paid';
    case PartiallyPaid = 'partially_paid';
    case Overdue       = 'overdue';
    case Cancelled     = 'cancelled';
    case Refunded      = 'refunded';

    /**
     * Get all status values as array.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if status is paid.
     */
    public function isPaid(): bool
    {
        return $this === self::Paid;
    }

    /**
     * Check if status is pending payment.
     */
    public function isPending(): bool
    {
        return in_array($this, [self::New, self::Pending, self::Overdue]);
    }

    /**
     * Get the translated label for the invoice status.
     */
    public function label(): string
    {
        return __('subscription-plans::subscription-plans.invoice_status.'.$this->value);
    }
}
