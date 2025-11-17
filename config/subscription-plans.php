<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tax Rate
    |--------------------------------------------------------------------------
    |
    | Default tax rate for subscriptions (15% = 0.15)
    |
    */
    'tax_rate' => env('SUBSCRIPTION_TAX_RATE', 0.15),

    /*
    |--------------------------------------------------------------------------
    | Model Classes
    |--------------------------------------------------------------------------
    |
    | Customize model classes if you need to extend them
    |
    */
    'models' => [
        'plan'                      => \NootPro\SubscriptionPlans\Models\Plan::class,
        'plan_subscription'         => \NootPro\SubscriptionPlans\Models\PlanSubscription::class,
        'plan_feature'              => \NootPro\SubscriptionPlans\Models\PlanFeature::class,
        'plan_subscription_usage'   => \NootPro\SubscriptionPlans\Models\PlanSubscriptionUsage::class,
        'plan_subscription_feature' => \NootPro\SubscriptionPlans\Models\PlanSubscriptionFeature::class,
        'plan_module'               => \NootPro\SubscriptionPlans\Models\PlanModule::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Enum Classes
    |--------------------------------------------------------------------------
    |
    | Customize enum classes if you need to extend them
    |
    */
    'enums' => [
        'interval'           => \NootPro\SubscriptionPlans\Enums\Interval::class,
        'plan_type'          => \NootPro\SubscriptionPlans\Enums\PlanType::class,
        'subscription_model' => \NootPro\SubscriptionPlans\Enums\SubscriptionModel::class,
        'features'           => \NootPro\SubscriptionPlans\Enums\Features::class,
        'modules'            => \NootPro\SubscriptionPlans\Enums\Modules::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    |
    | Customize table names if needed
    |
    */
    'table_names' => [
        'plans'                      => 'plans',
        'plan_features'              => 'plan_features',
        'plan_subscriptions'         => 'plan_subscriptions',
        'plan_subscription_usage'    => 'plan_subscription_usage',
        'plan_subscription_features' => 'plan_subscription_features',
        'plan_modules'               => 'plan_modules',
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Configure feature behavior
    |
    */
    'features' => [
        'allow_unlimited'  => true, // Allow -1 for unlimited
        'auto_reset_usage' => true, // Auto-reset usage when period expires
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching behavior for subscriptions
    |
    */
    'cache' => [
        'enabled' => env('SUBSCRIPTION_CACHE_ENABLED', true),
        'ttl'     => env('SUBSCRIPTION_CACHE_TTL', 30), // Cache TTL in minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Configuration
    |--------------------------------------------------------------------------
    |
    | Configure middleware behavior
    |
    */
    'middleware' => [
        'redirect_route' => env('SUBSCRIPTION_REDIRECT_ROUTE', 'subscription.plans'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Event Listeners
    |--------------------------------------------------------------------------
    |
    | Control package event listeners and allow applications to extend/override.
    |
    | - enabled: When false, disables all default package listeners.
    | - additional: An associative array of event FQCN => array of listener FQCNs
    |   that should be registered in addition to the defaults (or alone if
    |   enabled is false).
    |
    */
    'listeners' => [
        'enabled'    => env('SUBSCRIPTION_LISTENERS_ENABLED', true),
        'additional' => [
            // \NootPro\SubscriptionPlans\Events\SubscriptionCreated::class => [
            //     \App\Listeners\YourCustomListener::class,
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Subscriber Resolver
    |--------------------------------------------------------------------------
    |
    | Custom callback to resolve the subscriber from the request.
    | This is useful if you have a custom way of determining the subscriber.
    |
    | Example:
    | 'subscriber_resolver' => function ($request) {
    |     return $request->user()->company;
    | },
    |
    */
    'subscriber_resolver' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache TTL (Legacy - use cache.ttl instead)
    |--------------------------------------------------------------------------
    |
    | @deprecated Use cache.ttl instead
    |
    */
    'cache_ttl' => env('SUBSCRIPTION_CACHE_TTL', 30),

    /*
    |--------------------------------------------------------------------------
    | Notifications Configuration
    |--------------------------------------------------------------------------
    |
    | Configure notification behavior for subscription events
    |
    */
    'notifications' => [
        'enabled'  => env('SUBSCRIPTION_NOTIFICATIONS_ENABLED', true),
        'channels' => ['mail', 'database'], // Available: mail, database, slack, etc.
    ],
];
