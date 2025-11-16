<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Console\Commands;

use Illuminate\Console\Command;
use NootPro\SubscriptionPlans\Models\PlanSubscription;

/**
 * Command to check and report subscriptions that are expiring soon.
 */
class CheckExpiringSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expiring 
                            {--days=7 : Number of days to look ahead}
                            {--trial : Check expiring trials instead of periods}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check subscriptions that are expiring soon';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days       = (int) $this->option('days');
        $checkTrial = $this->option('trial');

        if ($checkTrial) {
            $subscriptions = PlanSubscription::findEndingTrial($days)
                ->with(['plan', 'subscriber'])
                ->get();

            $this->info("Found {$subscriptions->count()} trials ending in the next {$days} days:");
        } else {
            $subscriptions = PlanSubscription::findEndingPeriod($days)
                ->with(['plan', 'subscriber'])
                ->get();

            $this->info("Found {$subscriptions->count()} subscriptions ending in the next {$days} days:");
        }

        if ($subscriptions->isEmpty()) {
            $this->info('No expiring subscriptions found.');

            return Command::SUCCESS;
        }

        $tableData = $subscriptions->map(function ($subscription) use ($checkTrial) {
            $subscriber = $subscription->subscriber;
            $expiresAt  = $checkTrial ? $subscription->trial_ends_at : $subscription->ends_at;

            return [
                'ID'         => $subscription->id,
                'Subscriber' => $subscriber ? get_class($subscriber).' #'.$subscriber->getKey() : 'N/A',
                'Plan'       => $subscription->plan->name          ?? 'N/A',
                'Expires At' => $expiresAt?->format('Y-m-d H:i:s') ?? 'N/A',
                'Status'     => $subscription->active() ? 'Active' : 'Inactive',
            ];
        })->toArray();

        $this->table(
            ['ID', 'Subscriber', 'Plan', 'Expires At', 'Status'],
            $tableData
        );

        return Command::SUCCESS;
    }
}
