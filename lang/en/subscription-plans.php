<?php

return [
    'modules' => [
        'website_content' => 'Website Content',
        'catalog' => 'Catalog',
    ],

    'features' => [
        'users' => 'Users',
    ],

    'interval' => [
        'day' => 'Day',
        'week' => 'Week',
        'month' => 'Month',
        'year' => 'Year',
    ],

    'plan-type' => [
        'plan' => 'Plan',
        'test_plan' => 'Test Plan',
    ],

    'subscription-model' => [
        'payg' => 'Pay As You Go',
        'fixed' => 'Fixed',
    ],

    'no_active_subscription' => 'You do not have an active subscription.',

    'validation' => [
        'feature_value_numeric' => 'The feature value must be a number.',
        'feature_value_min' => 'The feature value must be at least -1 (unlimited).',
        'feature_value_unlimited_not_allowed' => 'Unlimited features are not allowed.',
    ],

    'notifications' => [
        'subscription_created' => [
            'subject' => 'Welcome to :plan',
            'greeting' => 'Hello!',
            'line1' => 'Thank you for subscribing to :plan.',
            'line2' => 'Your subscription is active from :start_date to :end_date.',
            'trial' => 'Your trial period ends on :trial_end.',
        ],
        'subscription_expiring' => [
            'subject' => 'Your Subscription is Expiring Soon',
            'greeting' => 'Hello!',
            'line1' => 'Your :plan subscription will expire in :days days.',
            'line2' => 'Your subscription ends on :end_date.',
        ],
        'subscription_expired' => [
            'subject' => 'Your Subscription has Expired',
            'greeting' => 'Hello!',
            'line1' => 'Your :plan subscription has expired.',
            'line2' => 'To continue enjoying our services, please renew your subscription.',
        ],
        'trial_ending' => [
            'subject' => 'Your Trial is Ending Soon',
            'greeting' => 'Hello!',
            'line1' => 'Your :plan trial will end in :days days.',
            'line2' => 'Your trial ends on :trial_end.',
        ],
        'view_subscription' => 'View Subscription',
        'renew_subscription' => 'Renew Subscription',
        'subscribe_now' => 'Subscribe Now',
        'view_plans' => 'View Plans',
        'thank_you' => 'Thank you for using our application!',
    ],
];

