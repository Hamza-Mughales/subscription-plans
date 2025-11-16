<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NootPro\SubscriptionPlans\Enums\SubscriptionModel;
use NootPro\SubscriptionPlans\Models\Plan;
use NootPro\SubscriptionPlans\Models\PlanSubscription;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\NootPro\SubscriptionPlans\Models\PlanSubscription>
 */
class PlanSubscriptionFactory extends Factory
{
    protected $model = PlanSubscription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = $this->faker->dateTimeBetween('-1 month', 'now');
        $endsAt = (clone $startsAt)->modify('+1 month');

        return [
            'name' => $this->faker->words(2, true),
            'slug' => $this->faker->unique()->slug(),
            'plan_id' => Plan::factory(),
            'subscriber_type' => 'App\Models\User',
            'subscriber_id' => 1,
            'subscription_type' => SubscriptionModel::Fixed,
            'trial_ends_at' => null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'is_active' => true,
            'is_paid' => true,
        ];
    }

    /**
     * Indicate that the subscription is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the subscription is on trial.
     */
    public function onTrial(): static
    {
        return $this->state(function (array $attributes) {
            $trialEndsAt = $this->faker->dateTimeBetween('now', '+14 days');
            $startsAt = (clone $trialEndsAt)->modify('+1 day');
            $endsAt = (clone $startsAt)->modify('+1 month');

            return [
                'trial_ends_at' => $trialEndsAt,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
            ];
        });
    }

    /**
     * Indicate that the subscription is canceled.
     */
    public function canceled(): static
    {
        return $this->state(fn (array $attributes) => [
            'canceled_at' => now(),
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the subscription has ended.
     */
    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'ends_at' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'is_active' => false,
        ]);
    }
}

