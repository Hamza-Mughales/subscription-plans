<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use NootPro\SubscriptionPlans\Models\Invoice;
use NootPro\SubscriptionPlans\Models\PlanSubscription;

trait Invoiceable
{
    /**
     * Get subscription invoices relationship.
     *
     * @return HasMany<Invoice>
     */
    public function subscriptionInvoices(): HasMany
    {
        $subscriberKey = config('subscription-plans.foreign_keys.subscriber_id', 'subscriber_id');

        return $this->hasMany(
            config('subscription-plans.models.invoice'),
            $subscriberKey
        );
    }

    /**
     * Get last subscription not paid.
     *
     * Note: This method requires the model to use HasPlanSubscriptions trait.
     */
    public function getLastSubscriptionNotPaid(): ?PlanSubscription
    {
        if (! method_exists($this, 'planSubscriptions')) {
            return null;
        }

        $subscription = $this->planSubscriptions()
            ->whereNull('canceled_at')
            ->where('is_paid', false)
            ->where(function ($query): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->orderByDesc('id')
            ->first();

        return $subscription instanceof PlanSubscription ? $subscription : null;
    }
}
