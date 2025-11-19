<?php

return [
    'modules' => [
        'website_content' => 'إدارة الموقع',
        'catalog'         => 'كتالوج',
    ],

    'features' => [
        'users' => 'المستخدمون',
    ],

    'interval' => [
        'day'   => 'يوم',
        'week'  => 'أسبوع',
        'month' => 'شهر',
        'year'  => 'سنة',
    ],

    'plan-type' => [
        'plan'      => 'خطة',
        'test_plan' => 'خطة تجريبية',
    ],

    'subscription-model' => [
        'payg'  => 'ادفع عند الاستخدام',
        'fixed' => 'ثابت',
    ],

    'no_active_subscription' => 'ليس لديك اشتراك نشط.',

    'validation' => [
        'feature_value_numeric'               => 'يجب أن تكون قيمة الميزة رقماً.',
        'feature_value_min'                   => 'يجب أن تكون قيمة الميزة على الأقل -1 (غير محدود).',
        'feature_value_unlimited_not_allowed' => 'الميزات غير المحدودة غير مسموحة.',
    ],

    'notifications' => [
        'subscription_created' => [
            'subject'  => 'مرحباً بك في :plan',
            'greeting' => 'مرحباً!',
            'line1'    => 'شكراً لك على الاشتراك في :plan.',
            'line2'    => 'اشتراكك نشط من :start_date إلى :end_date.',
            'trial'    => 'تنتهي فترة التجربة الخاصة بك في :trial_end.',
        ],
        'subscription_expiring' => [
            'subject'  => 'اشتراكك على وشك الانتهاء',
            'greeting' => 'مرحباً!',
            'line1'    => 'سينتهي اشتراكك في :plan خلال :days أيام.',
            'line2'    => 'ينتهي اشتراكك في :end_date.',
        ],
        'subscription_expired' => [
            'subject'  => 'انتهى اشتراكك',
            'greeting' => 'مرحباً!',
            'line1'    => 'انتهى اشتراكك في :plan.',
            'line2'    => 'لمواصلة الاستمتاع بخدماتنا، يرجى تجديد اشتراكك.',
        ],
        'trial_ending' => [
            'subject'  => 'فترة التجربة الخاصة بك على وشك الانتهاء',
            'greeting' => 'مرحباً!',
            'line1'    => 'ستنتهي فترة التجربة الخاصة بك في :plan خلال :days أيام.',
            'line2'    => 'تنتهي فترة التجربة في :trial_end.',
        ],
        'view_subscription'  => 'عرض الاشتراك',
        'renew_subscription' => 'تجديد الاشتراك',
        'subscribe_now'      => 'اشترك الآن',
        'view_plans'         => 'عرض الخطط',
        'thank_you'          => 'شكراً لاستخدامك تطبيقنا!',
    ],

    'invoice_type' => [
        'subscription' => 'اشتراك',
    ],

    'invoice_status' => [
        'new'            => 'جديد',
        'pending'        => 'قيد الانتظار',
        'paid'           => 'مدفوع',
        'partially_paid' => 'مدفوع جزئياً',
        'overdue'        => 'متأخر',
        'cancelled'      => 'ملغي',
        'refunded'       => 'مسترد',
    ],

    'invoice_transaction_status' => [
        'pending'   => 'قيد الانتظار',
        'completed' => 'مكتمل',
        'failed'    => 'فاشل',
        'refunded'  => 'مسترد',
    ],

    'payment-method-type' => [
        'bank_transfer'  => 'تحويل بنكي',
        'online_payment' => 'دفع إلكتروني',
        'visa'           => 'فيزا',
    ],
];
