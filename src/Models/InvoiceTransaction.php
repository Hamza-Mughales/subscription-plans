<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use NootPro\SubscriptionPlans\Enums\InvoiceTransactionStatus;

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
        $this->table = config('subscription-plans.table_names.invoice_transactions', 'invoice_transactions'); // Table name is plural
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(
            config('subscription-plans.models.invoice'),
            'invoice_id'
        );
    }

    /**
     * Get subscriber through invoice relationship.
     */
    public function subscriber()
    {
        return $this->invoice->subscriber();
    }

    /**
     * Get payment method relationship (optional - payment_method can be string or reference).
     */
    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(
            config('subscription-plans.models.payment_method'),
            'payment_method',
            'slug'
        );
    }
}
