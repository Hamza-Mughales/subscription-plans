<?php

declare(strict_types=1);

use NootPro\SubscriptionPlans\Enums\Features;
use NootPro\SubscriptionPlans\Enums\Interval;
use NootPro\SubscriptionPlans\Enums\PlanType;
use NootPro\SubscriptionPlans\Enums\SubscriptionModel;
use NootPro\SubscriptionPlans\Models\Plan;
use NootPro\SubscriptionPlans\Models\PlanFeature;

beforeEach(function () {
    $this->plan = Plan::create([
        'name'               => ['en' => 'Test Plan'],
        'slug'               => 'test-plan',
        'price'              => 99.00,
        'currency'           => 'USD',
        'invoice_period'     => 1,
        'invoice_interval'   => Interval::Month,
        'subscription_model' => SubscriptionModel::Fixed,
        'type'               => PlanType::Plan,
    ]);
});

it('can create a plan feature', function () {
    $feature = $this->plan->features()->create([
        'code'                => Features::Users->value,
        'name'                => ['en' => 'Users'],
        'value'               => 10,
        'resettable_period'   => 1,
        'resettable_interval' => 'month',
        'sort_order'          => 1,
    ]);

    expect($feature)
        ->toBeInstanceOf(PlanFeature::class)
        ->code->toBe(Features::Users->value)
        ->value->toBe(10);
});

it('can get feature by slug', function () {
    $this->plan->features()->create([
        'code'  => Features::Users->value,
        'name'  => ['en' => 'Users'],
        'value' => 5,
    ]);

    $feature = $this->plan->getFeatureBySlug(Features::Users->value);

    expect($feature)
        ->not->toBeNull()
        ->code->toBe(Features::Users->value)
        ->value->toBe(5);
});

it('returns null for non-existent feature', function () {
    $feature = $this->plan->getFeatureBySlug('non-existent-feature');

    expect($feature)->toBeNull();
});

it('can handle unlimited feature value', function () {
    $feature = $this->plan->features()->create([
        'code'  => 'unlimited-feature',
        'name'  => ['en' => 'Unlimited Feature'],
        'value' => -1, // Unlimited
    ]);

    expect($feature->value)->toBe(-1);
});

it('can handle disabled feature value', function () {
    $feature = $this->plan->features()->create([
        'code'  => 'disabled-feature',
        'name'  => ['en' => 'Disabled Feature'],
        'value' => 0, // Disabled
    ]);

    expect($feature->value)->toBe(0);
});
