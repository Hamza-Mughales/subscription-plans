<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use HamzaMughales\Subscriptions\Enums\InvoiceTransactionStatus;

/**
 * InvoiceTransaction.
 *
 * @property int $id
 * @property int $invoice_id
 * @property float $amount
 * @property string|null $payment_method Payment method type (e.g., 'bank_transfer', 'visa') or reference to PaymentMethod model
 * @property string|null $transaction_id
 * @property InvoiceTransactionStatus $status
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \HamzaMughales\Subscriptions\Models\Invoice $invoice
 * @property-read \HamzaMughales\Subscriptions\Models\PaymentMethod|null $paymentMethod
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
     * Get payment method relationship.
     *
     * Note: The payment_method field stores the payment method type string (e.g., 'bank_transfer', 'visa').
     * This relationship matches the payment_method value to the PaymentMethod model's type field.
     * If no matching PaymentMethod exists, this relationship will return null.
     *
     * @return BelongsTo<PaymentMethod, $this>
     */
    public function paymentMethod(): BelongsTo
    {
        /** @var class-string<PaymentMethod> $modelClass */
        $modelClass = config('subscription-plans.models.payment_method');

        return $this->belongsTo($modelClass, 'payment_method', 'type');
    }
}
