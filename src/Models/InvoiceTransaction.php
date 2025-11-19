<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NootPro\SubscriptionPlans\Enums\InvoiceTransactionStatus;

/**
 * InvoiceTransaction.
 *
 * @property int $invoice_id
 * @property float $amount
 * @property string|null $payment_method
 * @property string|null $transaction_id
 * @property InvoiceTransactionStatus $status
 * @property string|null $notes
 */
class InvoiceTransaction extends Model
{
    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_method',
        'transaction_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => InvoiceTransactionStatus::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('subscription-plans.table_names.invoice_transactions', 'plan_invoice_transactions'); // Table name is plural
    }

    /**
     * @return BelongsTo<Invoice, $this>
     */
    public function invoice(): BelongsTo
    {
        /** @var class-string<Invoice> $modelClass */
        $modelClass = config('subscription-plans.models.invoice');

        return $this->belongsTo($modelClass, 'invoice_id');
    }

    /**
     * Get subscriber through invoice relationship.
     *
     * @return BelongsTo<Model, Invoice>
     */
    public function subscriber(): BelongsTo
    {
        /** @var Invoice $invoice */
        $invoice = $this->invoice;

        return $invoice->subscriber();
    }

    /**
     * Get payment method relationship (optional - payment_method can be string or reference).
     *
     * @return BelongsTo<PaymentMethod, $this>
     */
    public function paymentMethod(): BelongsTo
    {
        /** @var class-string<PaymentMethod> $modelClass */
        $modelClass = config('subscription-plans.models.payment_method');

        return $this->belongsTo($modelClass, 'payment_method', 'slug');
    }
}
