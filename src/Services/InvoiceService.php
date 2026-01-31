<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Services;

use HamzaMughales\Subscriptions\Enums\InvoiceStatus;
use HamzaMughales\Subscriptions\Enums\InvoiceTransactionStatus;
use HamzaMughales\Subscriptions\Models\Invoice;
use HamzaMughales\Subscriptions\Models\InvoiceItem;
use HamzaMughales\Subscriptions\Models\InvoiceTransaction;

class InvoiceService
{
    /**
     * Calculate tax for an amount.
     */
    public function calculateTax(float $amount, ?float $taxRate = null): float
    {
        $rate = $taxRate ?? config('subscription-plans.invoice.tax_rate', 0.15);

        return round($amount * $rate, 2);
    }

    /**
     * Create invoice for subscription.
     *
     * @param  mixed  $subscriber
     */
    public function createInvoiceForSubscription($subscriber, int $subscriptionId, float $amount, ?string $note = null): Invoice
    {
        $subscriberKey = config('subscription-plans.foreign_keys.subscriber_id', 'subscriber_id');
        $tax           = $this->calculateTax($amount);
        $dueDate       = now()->addDays(config('subscription-plans.invoice.default_due_days', 30));

        /** @var Invoice $invoice */
        $invoice = app(config('subscription-plans.models.invoice'))->create([
            'subscription_id' => $subscriptionId,
            $subscriberKey    => $subscriber->id ?? $subscriber,
            'amount'          => $amount,
            'tax'             => $tax,
            'status'          => InvoiceStatus::New,
            'due_date'        => $dueDate,
            'paid'            => false,
            'note'            => $note,
        ]);

        return $invoice;
    }

    /**
     * Add line item to invoice.
     */
    public function addItem(Invoice $invoice, string $description, float $unitPrice, int $quantity = 1): InvoiceItem
    {
        $total = $unitPrice * $quantity;

        /** @var InvoiceItem $item */
        $item = $invoice->items()->create([
            'description' => $description,
            'quantity'    => $quantity,
            'unit_price'  => $unitPrice,
            'total'       => $total,
        ]);

        return $item;
    }

    /**
     * Record a payment transaction for an invoice.
     */
    public function recordPayment(
        Invoice $invoice,
        float $amount,
        ?string $paymentMethod = null,
        ?string $transactionId = null,
        ?string $notes = null,
        InvoiceTransactionStatus $status = InvoiceTransactionStatus::Completed
    ): InvoiceTransaction {
        /** @var InvoiceTransaction $transaction */
        $transaction = $invoice->transactions()->create([
            'amount'         => $amount,
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'status'         => $status,
            'notes'          => $notes,
        ]);

        // Update invoice payment status
        $this->updateInvoicePaymentStatus($invoice);

        return $transaction;
    }

    /**
     * Mark invoice as paid.
     */
    public function markAsPaid(Invoice $invoice): bool
    {
        return $invoice->update([
            'paid'   => true,
            'status' => InvoiceStatus::Paid,
        ]);
    }

    /**
     * Mark invoice as partially paid.
     */
    public function markAsPartiallyPaid(Invoice $invoice): bool
    {
        return $invoice->update([
            'status' => InvoiceStatus::PartiallyPaid,
        ]);
    }

    /**
     * Cancel an invoice.
     */
    public function cancel(Invoice $invoice, ?string $note = null): bool
    {
        return $invoice->update([
            'status' => InvoiceStatus::Cancelled,
            'note'   => $note ? ($invoice->note ? $invoice->note."\n".$note : $note) : $invoice->note,
        ]);
    }

    /**
     * Update invoice payment status based on transactions.
     */
    protected function updateInvoicePaymentStatus(Invoice $invoice): void
    {
        $totalPaid = $invoice->transactions()
            ->where('status', InvoiceTransactionStatus::Completed)
            ->sum('amount');

        $totalAmount = $invoice->total;

        if ($totalPaid >= $totalAmount) {
            $this->markAsPaid($invoice);
        } elseif ($totalPaid > 0) {
            $this->markAsPartiallyPaid($invoice);
        }
    }

    /**
     * Get total paid amount for an invoice.
     */
    public function getTotalPaid(Invoice $invoice): float
    {
        return (float) $invoice->transactions()
            ->where('status', InvoiceTransactionStatus::Completed)
            ->sum('amount');
    }

    /**
     * Get remaining balance for an invoice.
     */
    public function getRemainingBalance(Invoice $invoice): float
    {
        $totalPaid = $this->getTotalPaid($invoice);

        return max(0, $invoice->total - $totalPaid);
    }
}
