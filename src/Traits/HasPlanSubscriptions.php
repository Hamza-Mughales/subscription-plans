<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use NootPro\SubscriptionPlans\Models\Plan;
use NootPro\SubscriptionPlans\Models\PlanSubscription;
use NootPro\SubscriptionPlans\Services\Period;

trait HasPlanSubscriptions
{
    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $name
     * @param  string  $type
     * @param  string  $id
     * @param  string  $localKey
     * @return MorphMany
     */
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    protected static function bootHasSubscriptions(): void
    {
        static::deleted(function (self $plan) {
            $plan->planSubscriptions()->delete();
        });
    }

    /**
     * The subscriber may have many plan subscriptions.
     *
     * @return MorphMany<PlanSubscription, $this>
     */
    public function planSubscriptions(): MorphMany
    {
        return $this->morphMany(PlanSubscription::class, 'subscriber', 'subscriber_type', 'subscriber_id');
    }

    /**
     * @return Collection<int, PlanSubscription>
     */
    public function activePlanSubscriptions(): Collection
    {
        return $this->planSubscriptions
            ->filter(fn (PlanSubscription $sub) => ! $sub->inactive());
    }

    public function planSubscription(string $subscriptionSlug): ?PlanSubscription
    {
        /** @var PlanSubscription|null $subscription */
        $subscription = $this->planSubscriptions()->where('slug', $subscriptionSlug)->first();

        return $subscription;
    }

    /**
     * @return Collection<int, Plan>
     */
    public function subscribedPlans(): Collection
    {
        $planIds = $this->planSubscriptions
            ->filter(fn (PlanSubscription $sub) => ! $sub->trashed() && ! $sub->inactive())
            ->pluck('plan_id')
            ->unique();

        /** @var Builder<Plan> $query */
        $query = app(Plan::class);

        return $query->whereIn('id', $planIds)->get();
    }

    /**
     * Check if the subscriber subscribed to the given plan.
     *
     * @param  int  $planId
     */
    public function subscribedTo($planId): bool
    {
        $subscription = $this->planSubscriptions()->where('plan_id', $planId)->first();

        return $subscription && $subscription->active();
    }

    public function newPlanSubscription(string $subscription, Plan $plan, ?Carbon $startDate = null): PlanSubscription
    {
        // Deactivate all active subscriptions before creating a new one
        // This ensures only one subscription is active at a time
        $this->planSubscriptions()
            ->where('is_active', true)
            ->get()
            ->each(function (PlanSubscription $activeSubscription) {
                $activeSubscription->cancel(immediately: true);
            });

        $trial  = new Period($plan->trial_interval->value, $plan->trial_period, $startDate ?? now());
        $period = new Period($plan->invoice_interval->value, $plan->invoice_period, $trial->getEndDate());

        /** @var PlanSubscription $subscriptionModel */
        $subscriptionModel = $this->planSubscriptions()->create([
            'name'              => $subscription,
            'plan_id'           => $plan->getKey(),
            'subscription_type' => $plan->subscription_model->value,
            'trial_ends_at'     => $trial->getEndDate(),
            'starts_at'         => $period->getStartDate(),
            'ends_at'           => $period->getEndDate(),
            'is_active'         => true,
        ]);

        return $subscriptionModel;
    }

    /**
     * Get the active subscription for this subscriber.
     */
    public function activePlanSubscription(): ?PlanSubscription
    {
        return $this->planSubscriptions()
            ->where('is_active', true)
            ->first();
    }
}
