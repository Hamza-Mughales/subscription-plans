<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Validation rule for feature values.
 *
 * Validates that a feature value is either:
 * - -1 (unlimited)
 * - 0 (disabled)
 * - A positive integer (limited quantity)
 */
class ValidFeatureValue implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            $fail(__('subscription-plans::subscription-plans.validation.feature_value_numeric'));

            return;
        }

        $value = (int) $value;

        // Allow -1 (unlimited), 0 (disabled), or positive integers
        if ($value < -1) {
            $fail(__('subscription-plans::subscription-plans.validation.feature_value_min'));

            return;
        }

        // Check if unlimited is allowed
        if ($value === -1 && ! config('subscription-plans.features.allow_unlimited', true)) {
            $fail(__('subscription-plans::subscription-plans.validation.feature_value_unlimited_not_allowed'));
        }
    }
}
