# Laravel Subscription Plans
A comprehensive, flexible, and production-ready subscription and plans management system for Laravel applications. Perfect for SaaS applications, membership sites, and any project requiring subscription-based access control.

## Features

### Core Features
- ✅ Multiple subscription plans with pricing
- ✅ Feature-based limits (unlimited, disabled, or numeric)
- ✅ Usage tracking with automatic resets
- ✅ Trial periods
- ✅ Grace periods
- ✅ Plan upgrades/downgrades with proration
- ✅ Multilingual support (Arabic, English)
- ✅ Polymorphic subscribers (Company, User, etc.)
- ✅ Soft deletes for history
- ✅ Event-driven architecture
- ✅ Module-based access control

### Advanced Features
- ✅ **Subscription Middleware** - Protect routes with subscription validation
- ✅ **Console Commands** - Automated subscription maintenance tasks

## Installation

### Via Composer

```bash
composer require noot-web/subscription-plans
```

### Publish Migrations

```bash
php artisan vendor:publish --tag=subscription-plans-migrations
php artisan migrate
```

### Publish Config

```bash
php artisan vendor:publish --tag=subscription-plans-config
```

## Quick Start

### 1. Add Trait to Your Model

Add the `HasPlanSubscriptions` trait to your subscriber model (e.g., User, Company):

```php
use NootPro\SubscriptionPlans\Traits\HasPlanSubscriptions;

class Company extends Model
{
    use HasPlanSubscriptions;
}
```

### 2. Create a Plan

```php
use NootPro\SubscriptionPlans\Models\Plan;
use NootPro\SubscriptionPlans\Enums\SubscriptionModel;
use NootPro\SubscriptionPlans\Enums\PlanType;
use NootPro\SubscriptionPlans\Enums\Interval;

$plan = Plan::create([
    'name' => ['en' => 'Pro Plan'],
    'slug' => 'pro-plan',
    'price' => 99.00,
    'currency' => 'USD',
    'invoice_period' => 1,
    'invoice_interval' => Interval::Month,
    'trial_period' => 14,
    'trial_interval' => Interval::Day,
    'subscription_model' => SubscriptionModel::Fixed,
    'type' => PlanType::Plan,
    'is_active' => true,
    'is_visible' => true,
]);
```

### 3. Add Features to Plan

```php
use NootPro\SubscriptionPlans\Enums\Features;
use NootPro\SubscriptionPlans\Enums\Interval;

$plan->features()->create([
    'slug' => Features::Users->value,
    'name' => ['en' => 'Users'],
    'value' => 10, // 10 users allowed
    'resettable_period' => 0, // No reset
    'resettable_interval' => Interval::Month,
    'sort_order' => 1,
]);
```

### 4. Create Subscription

```php
$company = Company::find(1);
$plan = Plan::where('slug', 'pro-plan')->first();

$subscription = $company->newPlanSubscription('main', $plan);
```

### 5. Track Usage

```php
// Record usage
$subscription->recordFeatureUsage(Features::Users->value, 1);

// Check if can use
if ($subscription->canUseFeature(Features::Users->value)) {
    // Create user
}

// Get remaining
$remaining = $subscription->getFeatureRemainings(Features::Users->value);
```

## Events

The package fires events for subscription lifecycle:

- `SubscriptionCreated` - When a subscription is created
- `SubscriptionUpdated` - When a subscription is updated
- `SubscriptionDeleted` - When a subscription is deleted
- `SubscriptionRestored` - When a subscription is restored

Listen to events in your `EventServiceProvider`:

```php
use NootPro\SubscriptionPlans\Events\SubscriptionCreated;

protected $listen = [
    SubscriptionCreated::class => [
        // Your listeners
    ],
];
```

## Usage Examples

### Check Subscription Status

```php
$subscription->active();   // bool
$subscription->onTrial();  // bool
$subscription->canceled(); // bool
$subscription->ended();    // bool
```

### Cancel Subscription

```php
$subscription->cancel(); // Cancel at end of period
$subscription->cancel(immediately: true); // Cancel immediately
```

### Renew Subscription

```php
$subscription->renew();
```

### Change Plan

```php
$newPlan = Plan::where('slug', 'enterprise-plan')->first();
$subscription->changePlan($newPlan);
```

### Query Subscriptions

```php
use NootPro\SubscriptionPlans\Models\PlanSubscription;

// Get active subscription
$subscription = $company->activePlanSubscription();

// Get all active subscriptions
$activeSubscriptions = $company->activePlanSubscriptions();

// Check if subscribed to a plan
if ($company->subscribedTo($planId)) {
    // User has this plan
}

// Find subscriptions ending soon
PlanSubscription::findEndingPeriod(7)->get(); // Ending in 7 days
PlanSubscription::findEndingTrial(3)->get(); // Trial ending in 3 days
```

## Configuration

Edit `config/subscription-plans.php` to customize:

- Tax rate
- Model classes
- Enum classes
- Table names
- Feature behavior

## Requirements

- PHP >= 8.2
- Laravel >= 11.0
- MySQL 5.7+ / PostgreSQL 9.6+ / SQLite 3.8+

## Best Practices

This package follows Laravel and PHP best practices:

- ✅ PSR-12 Coding Standards
- ✅ Type Safety with strict types
- ✅ Mass Assignment Protection
- ✅ Database Transactions
- ✅ Event-Driven Architecture
- ✅ Comprehensive Testing (PEST)
- ✅ Soft Deletes for data integrity
- ✅ Polymorphic Relationships
- ✅ Query Scopes

## Advanced Usage

### Custom Subscriber Models

Any model can be a subscriber by using the trait:

```php
use NootPro\SubscriptionPlans\Traits\HasPlanSubscriptions;

class Team extends Model
{
    use HasPlanSubscriptions;
}
```

### Feature Limits

Features support three types of limits:

1. **Numeric** - Limited quantity (e.g., 10 users)
2. **Unlimited** - Value of -1 means no limit
3. **Disabled** - Value of 0 means feature is disabled

### Usage Reset Periods

Features can automatically reset usage after a period:

```php
$feature->update([
    'resettable_period' => 1,
    'resettable_interval' => 'month', // day, week, month, year
]);
```

### Proration Support

The package includes proration fields for handling mid-period plan changes:

- `prorate_day` - Day of month to prorate
- `prorate_period` - Proration period
- `prorate_extend_due` - Extend due date after proration

## Testing

This package uses **PEST** for testing - a delightful testing framework with a focus on simplicity.

Run the test suite:

```bash
composer test
```

Generate coverage report:

```bash
composer test-coverage
```

Run with profiling:

```bash
composer test-profile
```

Or use PEST directly:

```bash
vendor/bin/pest
vendor/bin/pest --parallel
vendor/bin/pest --coverage --min=80
```

## Security

If you discover any security issues, please email support@noot-web.com instead of using the issue tracker.

## Documentation

- **[README.md](README.md)** - Main documentation (you are here)
- **[IMPLEMENTATION_GUIDE.md](IMPLEMENTATION_GUIDE.md)** - Step-by-step implementation guide
- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Contribution guidelines
- **[CHANGELOG.md](CHANGELOG.md)** - Version history

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for recent changes.

## Credits

- [Hamza Mughales](https://github.com/Hamza-Mughales)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

- 📧 Email: support@noot-web.com
- 🐛 Issues: [GitHub Issues](https://github.com/noot-web/subscription-plans/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/noot-web/subscription-plans/discussions)
- 📖 Documentation: [Full Documentation](https://docs.noot-web.com)

