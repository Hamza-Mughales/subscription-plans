<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use NootPro\SubscriptionPlans\Providers\SubscriptionPlansServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'NootPro\\SubscriptionPlans\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            SubscriptionPlansServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        // Load migrations
        $migration = include __DIR__.'/../database/migrations/2022_03_05_200700_create_plans_table.php';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/2022_03_05_200701_create_plan_features_table.php';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/2022_03_05_200702_create_plan_subscriptions_table.php';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/2022_03_05_200703_create_plan_subscription_usage_table.php';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/2022_03_05_200704_create_plan_subscription_features_table.php';
        $migration->up();

        $migration = include __DIR__.'/../database/migrations/2025_08_05_105105_create_plan_modules_table.php';
        $migration->up();
    }
}
