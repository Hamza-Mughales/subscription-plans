<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use HamzaMughales\Subscriptions\Enums\PaymentMethodType;
use HamzaMughales\Subscriptions\Models\PaymentMethod;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\HamzaMughales\Subscriptions\Models\PaymentMethod>
 */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => [
                'en' => $this->faker->randomElement(['Bank Transfer', 'Credit Card', 'PayPal', 'Stripe']),
                'ar' => $this->faker->randomElement(['تحويل بنكي', 'بطاقة ائتمان', 'باي بال', 'سترايب']),
            ],
            'type'       => $this->faker->randomElement(PaymentMethodType::cases()),
            'is_active'  => true,
            'is_default' => false,
        ];
    }

    /**
     * Indicate that the payment method is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the payment method is the default.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }

    /**
     * Indicate the payment method type.
     */
    public function type(PaymentMethodType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}
