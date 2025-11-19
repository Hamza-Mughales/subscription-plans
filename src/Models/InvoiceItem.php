<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * InvoiceItem.
 *
 * @property int $invoice_id
 * @property string $description
 * @property int $quantity
 * @property float $unit_price
 * @property float $total
 */
class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'total',
    ];

    protected $casts = [
        'quantity'   => 'integer',
        'unit_price' => 'decimal:2',
        'total'      => 'decimal:2',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (InvoiceItem $item): void {
            if (empty($item->total) || $item->isDirty(['quantity', 'unit_price'])) {
                $item->total = $item->calculateTotal();
            }
        });
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('subscription-plans.table_names.invoice_items', 'plan_invoice_items');
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
     * Calculate total from quantity and unit_price.
     */
    public function calculateTotal(): float
    {
        return (float) ($this->quantity * $this->unit_price);
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
}
