<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use NootPro\SubscriptionPlans\Models\PlanSubscription;

/**
 * Command to deactivate subscriptions that have ended.
 */
class DeactivateExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:deactivate-expired 
                            {--dry-run : Show what would be deactivated without actually deactivating}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate subscriptions that have ended';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        // Get all ended subscriptions that are still active
        $expiredSubscriptions = PlanSubscription::findEndedPeriod()
            ->where('is_active', true)
            ->with(['plan', 'subscriber'])
            ->get();

        if ($expiredSubscriptions->isEmpty()) {
            $this->info('No expired subscriptions found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$expiredSubscriptions->count()} expired subscriptions:");

        $tableData = $expiredSubscriptions->map(function ($subscription) {
            $subscriber = $subscription->subscriber;

            return [
                'ID'         => $subscription->id,
                'Subscriber' => $subscriber ? get_class($subscriber).' #'.$subscriber->getKey() : 'N/A',
                'Plan'       => $subscription->plan->name                      ?? 'N/A',
                'Ended At'   => $subscription->ends_at?->format('Y-m-d H:i:s') ?? 'N/A',
            ];
        })->toArray();

        $this->table(
            ['ID', 'Subscriber', 'Plan', 'Ended At'],
            $tableData
        );

        if ($dryRun) {
            $this->warn('Dry run mode - no changes made.');

            return Command::SUCCESS;
        }

        if (! $this->confirm('Do you want to deactivate these subscriptions?', true)) {
            $this->info('Operation cancelled.');

            return Command::SUCCESS;
        }

        $deactivated = 0;
        DB::transaction(function () use ($expiredSubscriptions, &$deactivated) {
            foreach ($expiredSubscriptions as $subscription) {
                $subscription->is_active = false;
                $subscription->save();
                $deactivated++;
            }
        });

        $this->info("Successfully deactivated {$deactivated} subscriptions.");

        return Command::SUCCESS;
    }
}
