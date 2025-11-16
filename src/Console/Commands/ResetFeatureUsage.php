<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Console\Commands;

use Illuminate\Console\Command;
use NootPro\SubscriptionPlans\Models\PlanSubscriptionUsage;

/**
 * Command to reset expired feature usage records.
 */
class ResetFeatureUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:reset-usage 
                            {--dry-run : Show what would be reset without actually resetting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset expired feature usage records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        // Get all expired usage records
        $expiredUsages = PlanSubscriptionUsage::query()
            ->with(['subscription.plan', 'feature'])
            ->get()
            ->filter(fn ($usage) => $usage->expired());

        if ($expiredUsages->isEmpty()) {
            $this->info('No expired usage records found.');

            return Command::SUCCESS;
        }

        $this->info("Found {$expiredUsages->count()} expired usage records:");

        $tableData = $expiredUsages->map(function ($usage) {
            return [
                'ID'           => $usage->id,
                'Subscription' => $usage->subscription_id,
                'Feature'      => $usage->feature->name ?? 'N/A',
                'Used'         => $usage->used,
                'Expires At'   => $usage->expires_at?->format('Y-m-d H:i:s') ?? 'N/A',
            ];
        })->toArray();

        $this->table(
            ['ID', 'Subscription', 'Feature', 'Used', 'Expires At'],
            $tableData
        );

        if ($dryRun) {
            $this->warn('Dry run mode - no changes made.');

            return Command::SUCCESS;
        }

        if (! $this->confirm('Do you want to reset these usage records?', true)) {
            $this->info('Operation cancelled.');

            return Command::SUCCESS;
        }

        $reset = 0;
        foreach ($expiredUsages as $usage) {
            $usage->used       = 0;
            $usage->expires_at = null;
            $usage->save();
            $reset++;
        }

        $this->info("Successfully reset {$reset} usage records.");

        return Command::SUCCESS;
    }
}
