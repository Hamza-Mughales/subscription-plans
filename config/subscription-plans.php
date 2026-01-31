<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Model Classes
    |--------------------------------------------------------------------------
    |
    | Customize model classes if you need to extend them
    |
    */
    'models' => [
        'plan'                      => \HamzaMughales\Subscriptions\Models\Plan::class,
        'plan_subscription'         => \HamzaMughales\Subscriptions\Models\PlanSubscription::class,
        'plan_feature'              => \HamzaMughales\Subscriptions\Models\PlanFeature::class,
        'plan_subscription_usage'   => \HamzaMughales\Subscriptions\Models\PlanSubscriptionUsage::class,
        'plan_subscription_feature' => \HamzaMughales\Subscriptions\Models\PlanSubscriptionFeature::class,
        'plan_module'               => \HamzaMughales\Subscriptions\Models\PlanModule::class,
        'invoice'                   => \HamzaMughales\Subscriptions\Models\Invoice::class,
        'invoice_item'              => \HamzaMughales\Subscriptions\Models\InvoiceItem::class,
        'invoice_transaction'       => \HamzaMughales\Subscriptions\Models\InvoiceTransaction::class,
        'payment_method'            => \HamzaMughales\Subscriptions\Models\PaymentMethod::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Model
    |--------------------------------------------------------------------------
    |
    | The model class that represents the tenant/company in your application.
    | This is used by the ModulesGate trait to resolve the tenant for module checks.
    |
    | Example: \App\Models\Company::class
    |
    */
    'tenant_model' => env('SUBSCRIPTION_TENANT_MODEL', null),

    /*
    |--------------------------------------------------------------------------
    | Enum Classes
    |--------------------------------------------------------------------------
    |
    | Customize enum classes if you need to extend them
    |
    */
    'enums' => [
        'interval'           => \HamzaMughales\Subscriptions\Enums\Interval::class,
        'plan_type'          => \HamzaMughales\Subscriptions\Enums\PlanType::class,
        'subscription_model' => \HamzaMughales\Subscriptions\Enums\SubscriptionModel::class,
        'features'           => \HamzaMughales\Subscriptions\Enums\Features::class,
        'modules'            => \HamzaMughales\Subscriptions\Enums\Modules::class,
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
        'plans'                      => 'plan_plans',
        'plan_features'              => 'plan_features',
        'plan_subscriptions'         => 'plan_subscriptions',
        'plan_subscription_usage'    => 'plan_subscription_usage',
        'plan_subscription_features' => 'plan_subscription_features',
        'plan_modules'               => 'plan_modules',
        'invoices'                   => 'plan_invoices',
        'invoice_items'              => 'plan_invoice_items',
        'invoice_transactions'       => 'plan_invoice_transactions',
        'payment_methods'            => 'plan_payment_methods',
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
            // \HamzaMughales\Subscriptions\Events\SubscriptionCreated::class => [
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
    | Tax Rate (Legacy - use invoice.tax_rate instead)
    |--------------------------------------------------------------------------
    |
    | @deprecated Use invoice.tax_rate instead
    |
    */
    'tax_rate' => env('SUBSCRIPTION_TAX_RATE', 0.15),

    /*
    |--------------------------------------------------------------------------
    | Foreign Keys
    |--------------------------------------------------------------------------
    |
    | Configure foreign key column names for tenant/subscriber relationships
    |
    */
    'foreign_keys' => [
        'subscriber_id' => env('SUBSCRIPTION_SUBSCRIBER_FOREIGN_KEY', 'subscriber_id'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoice Configuration
    |--------------------------------------------------------------------------
    |
    | Configure invoice-related settings
    |
    */
    'invoice' => [
        'tax_rate'         => env('SUBSCRIPTION_TAX_RATE', 0.15),
        'default_status'   => 'new',
        'default_due_days' => 30,
        'number_prefix'    => env('SUBSCRIPTION_INVOICE_PREFIX', 'INV'),
    ],
];
