<?php

namespace HamzaMughales\Subscriptions\Providers;

use Illuminate\Support\ServiceProvider;
use HamzaMughales\Subscriptions\Models\PlanSubscription;
use HamzaMughales\Subscriptions\Observers\PlanSubscriptionObserver;
use HamzaMughales\Subscriptions\Services\SubscriptionPlansService;

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
