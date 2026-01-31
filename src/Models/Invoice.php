<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use HamzaMughales\Subscriptions\Enums\InvoiceStatus;

/**
 * Invoice.
 *
 * @property string $invoice_number
 * @property int $subscription_id
 * @property float $amount
 * @property float $tax
 * @property InvoiceStatus $status
 * @property Carbon|null $due_date
 * @property Carbon|null $exp_date
 * @property bool $paid
 * @property string|null $note
 * @property-read float $total
 *
 * @method static Builder|Invoice paid()
 * @method static Builder|Invoice unpaid()
 * @method static Builder|Invoice overdue()
 * @method static Builder|Invoice status(InvoiceStatus $status)
 * @method static Builder|Invoice forSubscriber($subscriber)
 */
class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'subscription_id',
        'amount',
        'tax',
        'status',
        'due_date',
        'exp_date',
        'paid',
        'note',
    ];

    protected $casts = [
        'due_date' => 'date',
        'exp_date' => 'date',
        'paid'     => 'boolean',
        'amount'   => 'decimal:2',
        'tax'      => 'decimal:2',
        'status'   => InvoiceStatus::class,
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Invoice $invoice): void {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('subscription-plans.table_names.invoices', 'plan_invoices');

        // Add subscriber key to fillable dynamically
        $subscriberKey = config('subscription-plans.foreign_keys.subscriber_id', 'subscriber_id');
        if (! in_array($subscriberKey, $this->fillable)) {
            $this->fillable[] = $subscriberKey;
        }
    }

    /**
     * Generate a unique invoice number.
     */
    public static function generateInvoiceNumber(): string
    {
        $prefix = config('subscription-plans.invoice.number_prefix', 'INV');
        $year   = now()->format('Y');
        $month  = now()->format('m');

        $lastInvoice = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderByDesc('id')
            ->first();

        if ($lastInvoice && $lastInvoice->invoice_number) {
            // Extract sequence from last invoice number (format: PREFIX-YYYYMM-XXXXXX)
            $parts    = explode('-', $lastInvoice->invoice_number);
            $sequence = isset($parts[2]) ? ((int) $parts[2]) + 1 : 1;
        } else {
            $sequence = 1;
        }

        return sprintf('%s-%s%s-%06d', $prefix, $year, $month, $sequence);
    }

    /**
     * @return HasMany<InvoiceItem, $this>
     */
    public function items(): HasMany
    {
        /** @var class-string<InvoiceItem> $modelClass */
        $modelClass = config('subscription-plans.models.invoice_item');

        return $this->hasMany($modelClass, 'invoice_id');
    }

    /**
     * @return HasMany<InvoiceTransaction, $this>
     */
    public function transactions(): HasMany
    {
        /** @var class-string<InvoiceTransaction> $modelClass */
        $modelClass = config('subscription-plans.models.invoice_transaction');

        return $this->hasMany($modelClass, 'invoice_id');
    }

    /**
     * @return BelongsTo<PlanSubscription, $this>
     */
    public function subscription(): BelongsTo
    {
        /** @var class-string<PlanSubscription> $modelClass */
        $modelClass = config('subscription-plans.models.plan_subscription');

        return $this->belongsTo($modelClass, 'subscription_id');
    }

    /**
     * Get the subscriber model (polymorphic or direct relationship)
     *
     * @return BelongsTo<Model, $this>
     */
    public function subscriber(): BelongsTo
    {
        $subscriberKey = config('subscription-plans.foreign_keys.subscriber_id', 'subscriber_id');
        /** @var class-string<Model> $modelClass */
        $modelClass = config('subscription-plans.tenant_model');

        return $this->belongsTo($modelClass, $subscriberKey);
    }

    /**
     * Get the total amount including tax.
     */
    public function getTotalAttribute(): float
    {
        return (float) ($this->amount + $this->tax);
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return ! $this->paid && $this->due_date && $this->due_date->isPast();
    }

    /**
     * Check if invoice is paid.
     */
    public function isPaid(): bool
    {
        return $this->paid || $this->status === InvoiceStatus::Paid;
    }

    /**
     * Scope a query to only include paid invoices.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('paid', true)->orWhere('status', InvoiceStatus::Paid);
    }

    /**
     * Scope a query to only include unpaid invoices.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('paid', false)->where('status', '!=', InvoiceStatus::Paid);
    }

    /**
     * Scope a query to only include overdue invoices.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('paid', false)
            ->where('due_date', '<', now())
            ->where('status', '!=', InvoiceStatus::Paid);
    }

    /**
     * Scope a query to filter by status.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeStatus(Builder $query, InvoiceStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by subscriber.
     *
     * @param  Builder<Invoice>  $query
     * @return Builder<Invoice>
     */
    public function scopeForSubscriber(Builder $query, Model|int $subscriber): Builder
    {
        $subscriberKey = config('subscription-plans.foreign_keys.subscriber_id', 'subscriber_id');

        return $query->where($subscriberKey, $subscriber instanceof Model ? $subscriber->getKey() : $subscriber);
    }
}
