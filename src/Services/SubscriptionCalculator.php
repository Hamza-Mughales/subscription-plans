<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Services;

use Carbon\Carbon;
use HamzaMughales\Subscriptions\Models\Plan;
use HamzaMughales\Subscriptions\Models\PlanSubscription;

class SubscriptionCalculator
{
    /**
     * Calculate upgrade amount based on remaining subscription days.
     */
    public function calculateUpgradeAmount(PlanSubscription $currentSubscription, Plan $newPlan): float
    {
        if (! $currentSubscription->ends_at) {
            return (float) $newPlan->price;
        }

        $remainingDays = max(0, Carbon::now()->diffInDays($currentSubscription->ends_at, false));

        if ($remainingDays <= 0) {
            return (float) $newPlan->price;
        }

        $currentPlan = $currentSubscription->plan;
        $totalDays   = $currentSubscription->starts_at
            ? Carbon::parse($currentSubscription->starts_at)->diffInDays($currentSubscription->ends_at)
            : 365;

        if ($totalDays <= 0) {
            return (float) $newPlan->price;
        }

        $currentPlanDailyRate = (float) $currentPlan->price / $totalDays;
        $newPlanDailyRate     = (float) $newPlan->price     / $totalDays;
        $creditAmount         = $currentPlanDailyRate * $remainingDays;
        $upgradeCost          = ($newPlanDailyRate * $remainingDays) - $creditAmount;

        return max(0, $upgradeCost);
    }

    /**
     * Calculate renewal amount.
     */
    public function calculateRenewalAmount(Plan $plan): float
    {
        return (float) $plan->price;
    }

    /**
     * Calculate subscription amount.
     */
    public function calculateSubscriptionAmount(Plan $plan): float
    {
        return (float) $plan->price;
    }

    /**
     * Calculate prorated amount for remaining days.
     */
    public function calculateProratedAmount(Plan $plan, int $totalDays, int $remainingDays): float
    {
        if ($totalDays <= 0) {
            return (float) $plan->price;
        }

        $dailyRate = (float) $plan->price / $totalDays;

        return $dailyRate * $remainingDays;
    }
}
