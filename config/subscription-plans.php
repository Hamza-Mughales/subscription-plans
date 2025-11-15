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
        'plan' => \NootPro\SubscriptionPlans\Models\Plan::class,
        'plan_subscription' => \NootPro\SubscriptionPlans\Models\PlanSubscription::class,
        'plan_feature' => \NootPro\SubscriptionPlans\Models\PlanFeature::class,
        'plan_subscription_usage' => \NootPro\SubscriptionPlans\Models\PlanSubscriptionUsage::class,
        'plan_module' => \NootPro\SubscriptionPlans\Models\PlanModule::class,
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
        'interval' => \NootPro\SubscriptionPlans\Enums\Interval::class,
        'plan_type' => \NootPro\SubscriptionPlans\Enums\PlanType::class,
        'subscription_model' => \NootPro\SubscriptionPlans\Enums\SubscriptionModel::class,
        'features' => \NootPro\SubscriptionPlans\Enums\Features::class,
        'modules' => \NootPro\SubscriptionPlans\Enums\Modules::class,
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
        'plans' => 'plans',
        'plan_features' => 'plan_features',
        'plan_subscriptions' => 'plan_subscriptions',
        'plan_subscription_usage' => 'plan_subscription_usage',
        'plan_modules' => 'plan_modules',
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
        'allow_unlimited' => true, // Allow -1 for unlimited
        'auto_reset_usage' => true, // Auto-reset usage when period expires
    ],
];

