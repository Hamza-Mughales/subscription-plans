<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NootPro\SubscriptionPlans\Enums\Interval;
use NootPro\SubscriptionPlans\Enums\PlanType;
use NootPro\SubscriptionPlans\Enums\SubscriptionModel;
use NootPro\SubscriptionPlans\Models\Plan;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\NootPro\SubscriptionPlans\Models\Plan>
 */
class PlanFactory extends Factory
{
    protected $model = Plan::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ['en' => $this->faker->words(2, true)],
            'slug' => $this->faker->unique()->slug(),
            'description' => ['en' => $this->faker->sentence()],
            'type' => PlanType::Plan,
            'is_active' => true,
            'is_visible' => true,
            'price' => $this->faker->randomFloat(2, 10, 500),
            'signup_fee' => 0,
            'subscription_model' => SubscriptionModel::Fixed,
            'currency' => 'USD',
            'trial_period' => 14,
            'trial_interval' => Interval::Day,
            'invoice_period' => 1,
            'invoice_interval' => Interval::Month,
            'grace_period' => 15,
            'grace_interval' => Interval::Day,
            'sort_order' => $this->faker->numberBetween(1, 100),
        ];
    }

    /**
     * Indicate that the plan is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the plan is hidden.
     */
    public function hidden(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_visible' => false,
        ]);
    }

    /**
     * Indicate that the plan is free.
     */
    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'price' => 0,
        ]);
    }
}

