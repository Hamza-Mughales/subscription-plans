<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use NootPro\SubscriptionPlans\Enums\InvoiceStatus;

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
        $this->table = config('subscription-plans.table_names.invoices', 'invoices');

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

    public function items(): HasMany
    {
        return $this->hasMany(
            config('subscription-plans.models.invoice_item'),
            'invoice_id'
        );
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(
            config('subscription-plans.models.invoice_transaction'),
            'invoice_id'
        );
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(
            config('subscription-plans.models.plan_subscription'),
            'subscription_id'
        );
    }

    /**
     * Get the subscriber model (polymorphic or direct relationship)
     */
    public function subscriber(): BelongsTo
    {
        $subscriberKey = config('subscription-plans.foreign_keys.subscriber_id', 'subscriber_id');

        return $this->belongsTo(
            config('subscription-plans.tenant_model'),
            $subscriberKey
        );
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
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('paid', true)->orWhere('status', InvoiceStatus::Paid);
    }

    /**
     * Scope a query to only include unpaid invoices.
     */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('paid', false)->where('status', '!=', InvoiceStatus::Paid);
    }

    /**
     * Scope a query to only include overdue invoices.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('paid', false)
            ->where('due_date', '<', now())
            ->where('status', '!=', InvoiceStatus::Paid);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeStatus(Builder $query, InvoiceStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by subscriber.
     */
    public function scopeForSubscriber(Builder $query, $subscriber): Builder
    {
        $subscriberKey = config('subscription-plans.foreign_keys.subscriber_id', 'subscriber_id');

        return $query->where($subscriberKey, $subscriber->id ?? $subscriber);
    }
}
