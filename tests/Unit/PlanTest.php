<?php

declare(strict_types=1);

use NootPro\SubscriptionPlans\Enums\Interval;
use NootPro\SubscriptionPlans\Enums\PlanType;
use NootPro\SubscriptionPlans\Enums\SubscriptionModel;
use NootPro\SubscriptionPlans\Models\Plan;

it('can create a plan', function () {
    $plan = Plan::create([
        'name'               => ['en' => 'Pro Plan'],
        'slug'               => 'pro-plan',
        'price'              => 99.00,
        'currency'           => 'USD',
        'invoice_period'     => 1,
        'invoice_interval'   => Interval::Month,
        'trial_period'       => 14,
        'trial_interval'     => Interval::Day,
        'subscription_model' => SubscriptionModel::Fixed,
        'type'               => PlanType::Plan,
        'is_active'          => true,
        'is_visible'         => true,
    ]);

    expect($plan)
        ->toBeInstanceOf(Plan::class)
        ->slug->toBe('pro-plan')
        ->is_active->toBeTrue();

    expect((float) $plan->price)->toBe(99.00);

    expect($plan->getTranslation('name', 'en'))->toBe('Pro Plan');
});

it('can check if plan is free', function () {
    $freePlan = Plan::create([
        'name'               => ['en' => 'Free Plan'],
        'slug'               => 'free-plan',
        'price'              => 0.00,
        'currency'           => 'USD',
        'invoice_period'     => 1,
        'invoice_interval'   => Interval::Month,
        'subscription_model' => SubscriptionModel::Fixed,
        'type'               => PlanType::Plan,
    ]);

    expect($freePlan->isFree())->toBeTrue();
});

it('can check if plan has trial', function () {
    $planWithTrial = Plan::create([
        'name'               => ['en' => 'Trial Plan'],
        'slug'               => 'trial-plan',
        'price'              => 50.00,
        'currency'           => 'USD',
        'invoice_period'     => 1,
        'invoice_interval'   => Interval::Month,
        'trial_period'       => 14,
        'trial_interval'     => Interval::Day,
        'subscription_model' => SubscriptionModel::Fixed,
        'type'               => PlanType::Plan,
    ]);

    expect($planWithTrial->hasTrial())->toBeTrue();
});

it('can activate and deactivate plan', function () {
    $plan = Plan::create([
        'name'               => ['en' => 'Test Plan'],
        'slug'               => 'test-plan',
        'price'              => 25.00,
        'currency'           => 'USD',
        'invoice_period'     => 1,
        'invoice_interval'   => Interval::Month,
        'subscription_model' => SubscriptionModel::Fixed,
        'type'               => PlanType::Plan,
        'is_active'          => false,
    ]);

    $plan->activate();
    expect($plan->fresh()->is_active)->toBeTrue();

    $plan->deactivate();
    expect($plan->fresh()->is_active)->toBeFalse();
});
