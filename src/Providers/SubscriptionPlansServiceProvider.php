<?php

namespace NootPro\SubscriptionPlans\Providers;

use Illuminate\Support\ServiceProvider;
use NootPro\SubscriptionPlans\Console\Commands\CheckExpiringSubscriptions;
use NootPro\SubscriptionPlans\Console\Commands\DeactivateExpiredSubscriptions;
use NootPro\SubscriptionPlans\Console\Commands\ResetFeatureUsage;
use NootPro\SubscriptionPlans\Models\PlanSubscription;
use NootPro\SubscriptionPlans\Observers\PlanSubscriptionObserver;
use NootPro\SubscriptionPlans\Services\SubscriptionPlansService;

class SubscriptionPlansServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/subscription-plans.php',
            'subscription-plans'
        );

        // Register service as singleton
        $this->app->singleton('subscription-plans', function ($app) {
            return new SubscriptionPlansService;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../../lang', 'subscription-plans');

        // Publish migrations
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../database/migrations' => database_path('migrations'),
            ], 'subscription-plans-migrations');

            // Publish config
            $this->publishes([
                __DIR__.'/../../config/subscription-plans.php' => config_path('subscription-plans.php'),
            ], 'subscription-plans-config');

            // Publish translations
            $this->publishes([
                __DIR__.'/../../lang' => $this->app->langPath('vendor/subscription-plans'),
            ], 'subscription-plans-translations');

            // Register commands
            $this->commands([
                CheckExpiringSubscriptions::class,
                ResetFeatureUsage::class,
                DeactivateExpiredSubscriptions::class,
            ]);
        }

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        // Register observers
        PlanSubscription::observe(PlanSubscriptionObserver::class);

        // Load helper functions
        if (file_exists($helperPath = __DIR__.'/../Helpers/subscription.php')) {
            require_once $helperPath;
        }
    }
}
