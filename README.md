# Subscription Plans
A comprehensive, flexible, and production-ready subscription and plans management system for Laravel applications. Perfect for SaaS applications, membership sites, and any project requiring subscription-based access control.

## Features

### Core Features
- ✅ Multiple subscription plans with pricing
- ✅ Feature-based limits (unlimited, disabled, or numeric)
- ✅ Usage tracking with automatic resets
- ✅ Trial periods
- ✅ Grace periods
- ✅ Plan upgrades/downgrades with proration
- ✅ **Invoice Management** - Complete invoicing system with transactions and payment tracking
- ✅ **Payment Methods** - Manage payment methods with translatable names and type-based categorization
- ✅ Multilingual support (Arabic, English)
- ✅ Polymorphic subscribers (Company, User, etc.)
- ✅ Soft deletes for history
- ✅ Event-driven architecture
- ✅ Module-based access control

### Advanced Features
- ✅ **Subscription Middleware** - Built-in `EnsureSubscriptionValid` middleware to protect routes with subscription validation

## Installation

### Via Composer

```bash
composer require hamza-mughales/laravel-subscriptions
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

### Publish Translations

```bash
php artisan vendor:publish --tag=subscription-plans-translations
```

## Quick Start

### 1. Add Trait to Your Model

Add the `HasPlanSubscriptions` trait to your subscriber model (e.g., User, Company):

```php
use HamzaMughales\SubscriptionPlans\Traits\HasPlanSubscriptions;

class Company extends Model
{
    use HasPlanSubscriptions;
}
```

### 2. Create a Plan

```php
use HamzaMughales\SubscriptionPlans\Models\Plan;
use HamzaMughales\SubscriptionPlans\Enums\SubscriptionModel;
use HamzaMughales\SubscriptionPlans\Enums\PlanType;
use HamzaMughales\SubscriptionPlans\Enums\Interval;

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
use HamzaMughales\SubscriptionPlans\Enums\Features;
use HamzaMughales\SubscriptionPlans\Enums\Interval;

$plan->features()->create([
    'code' => Features::Users->value,
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

$subscription = $company->newPlanSubscription($plan);
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
use HamzaMughales\SubscriptionPlans\Events\SubscriptionCreated;

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
use HamzaMughales\SubscriptionPlans\Models\PlanSubscription;

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

## Invoice Management

The package includes a comprehensive invoice management system for tracking subscription payments, transactions, and billing.

### Add Invoiceable Trait

Add the `Invoiceable` trait to your subscriber model to access invoice-related methods:

```php
use HamzaMughales\SubscriptionPlans\Traits\Invoiceable;

class Company extends Model
{
    use HasPlanSubscriptions, Invoiceable;
}
```

### Create Invoice

Create an invoice for a subscription using the `InvoiceService`:

```php
use HamzaMughales\SubscriptionPlans\Services\InvoiceService;

$invoiceService = app(InvoiceService::class);

$invoice = $invoiceService->createInvoiceForSubscription(
    subscriber: $company,
    subscriptionId: $subscription->id,
    amount: 99.00,
    note: 'Monthly subscription payment'
);
```

### Add Invoice Items

Add line items to an invoice:

```php
$invoiceService->addItem(
    invoice: $invoice,
    description: 'Pro Plan - Monthly Subscription',
    unitPrice: 99.00,
    quantity: 1
);
```

### Record Payments

Record payment transactions for an invoice:

```php
use HamzaMughales\SubscriptionPlans\Enums\InvoiceTransactionStatus;
use HamzaMughales\SubscriptionPlans\Enums\PaymentMethodType;

// Record payment with payment method type
$transaction = $invoiceService->recordPayment(
    invoice: $invoice,
    amount: 99.00,
    paymentMethod: PaymentMethodType::BankTransfer->value, // or 'bank_transfer'
    transactionId: 'txn_1234567890',
    notes: 'Payment processed successfully',
    status: InvoiceTransactionStatus::Completed
);

// Access payment method relationship
$paymentMethod = $transaction->paymentMethod; // Returns PaymentMethod model if exists
```

### Invoice Status Management

```php
// Mark invoice as paid
$invoiceService->markAsPaid($invoice);

// Mark invoice as partially paid
$invoiceService->markAsPartiallyPaid($invoice);

// Cancel an invoice
$invoiceService->cancel($invoice, 'Customer requested cancellation');

// Get payment information
$totalPaid = $invoiceService->getTotalPaid($invoice);
$remaining = $invoiceService->getRemainingBalance($invoice);
```

### Query Invoices

```php
use HamzaMughales\SubscriptionPlans\Models\Invoice;
use HamzaMughales\SubscriptionPlans\Enums\InvoiceStatus;

// Get all invoices for a subscriber
$invoices = $company->subscriptionInvoices;

// Get paid invoices
$paidInvoices = Invoice::paid()->get();

// Get unpaid invoices
$unpaidInvoices = Invoice::unpaid()->get();

// Get overdue invoices
$overdueInvoices = Invoice::overdue()->get();

// Filter by status
$newInvoices = Invoice::status(InvoiceStatus::New)->get();

// Filter by subscriber
$companyInvoices = Invoice::forSubscriber($company)->get();
```

### Invoice Relationships

```php
// Get invoice items
$items = $invoice->items;

// Get invoice transactions
$transactions = $invoice->transactions;

// Get related subscription
$subscription = $invoice->subscription;

// Get subscriber
$subscriber = $invoice->subscriber;

// Get total amount (amount + tax)
$total = $invoice->total;
```

### Invoice Statuses

Available invoice statuses:

- `New` - Newly created invoice
- `Pending` - Payment pending
- `Paid` - Fully paid
- `PartiallyPaid` - Partially paid
- `Overdue` - Payment overdue
- `Cancelled` - Invoice cancelled
- `Refunded` - Payment refunded

### Transaction Statuses

Available transaction statuses:

- `Pending` - Transaction pending
- `Completed` - Transaction completed
- `Failed` - Transaction failed
- `Refunded` - Transaction refunded

### Invoice Helper Methods

```php
// Check if invoice is paid
if ($invoice->isPaid()) {
    // Invoice is fully paid
}

// Check if invoice is overdue
if ($invoice->isOverdue()) {
    // Invoice is overdue
}

// Get invoice number (auto-generated)
$invoiceNumber = $invoice->invoice_number; // Format: INV-202501-000001
```

### Payment Methods

The package supports payment method management with translatable names and type-based categorization:

```php
use HamzaMughales\SubscriptionPlans\Models\PaymentMethod;
use HamzaMughales\SubscriptionPlans\Enums\PaymentMethodType;

// Create a payment method
$paymentMethod = PaymentMethod::create([
    'name' => ['en' => 'Bank Transfer', 'ar' => 'تحويل بنكي'],
    'type' => PaymentMethodType::BankTransfer,
    'is_active' => true,
    'is_default' => true,
]);

// Create other payment methods
PaymentMethod::create([
    'name' => ['en' => 'Online Payment', 'ar' => 'دفع إلكتروني'],
    'type' => PaymentMethodType::OnlinePayment,
    'is_active' => true,
    'is_default' => false,
]);

PaymentMethod::create([
    'name' => ['en' => 'Visa', 'ar' => 'فيزا'],
    'type' => PaymentMethodType::Visa,
    'is_active' => true,
    'is_default' => false,
]);

// Get payment method for transaction
$method = $transaction->paymentMethod;

// Query payment methods
$activeMethods = PaymentMethod::where('is_active', true)->get();
$defaultMethod = PaymentMethod::where('is_default', true)->first();

// Get payment method type label (translated)
$label = $paymentMethod->type->getLabel(); // Returns translated label
```

#### Available Payment Method Types

The package includes the following payment method types via the `PaymentMethodType` enum:

- `BankTransfer` - Bank transfer payments
- `OnlinePayment` - Online payment gateway
- `Visa` - Visa card payments

#### Payment Method Features

- **Translatable Names**: Payment method names support multiple languages
- **Type Safety**: Uses enum for type validation
- **Default Method**: Only one payment method can be set as default (automatically managed)
- **Active Status**: Control which payment methods are available
- **Transaction Integration**: Payment methods are linked to invoice transactions via the `type` field

### Invoice Configuration

Configure invoice settings in `config/subscription-plans.php`:

```php
'invoice' => [
    'tax_rate'         => env('SUBSCRIPTION_TAX_RATE', 0.15), // 15% tax
    'default_status'   => 'new',
    'default_due_days' => 30, // Invoice due in 30 days
    'number_prefix'    => env('SUBSCRIPTION_INVOICE_PREFIX', 'INV'),
],
```

### Calculate Tax

Calculate tax for any amount:

```php
$tax = $invoiceService->calculateTax(100.00); // Uses default tax rate
$tax = $invoiceService->calculateTax(100.00, 0.20); // Custom 20% tax rate
```

## Configuration

Edit `config/subscription-plans.php` to customize:

- Tax rate and invoice settings
- Model classes (including invoice models)
- Enum classes
- Table names (including invoice tables)
- Feature behavior
- Invoice configuration (tax rate, default status, due days, number prefix)

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
use HamzaMughales\SubscriptionPlans\Traits\HasPlanSubscriptions;

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


### Subscription Middleware

The package includes a built-in middleware `EnsureSubscriptionValid` to protect routes:

```php
// Register in bootstrap/app.php (Laravel 11+)
use HamzaMughales\SubscriptionPlans\Http\Middleware\EnsureSubscriptionValid;

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'subscription' => EnsureSubscriptionValid::class,
    ]);
})

// Use in routes
Route::middleware(['auth', 'subscription'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

The middleware automatically:
- Resolves the subscriber from the request (via config or auto-detection)
- Checks for active subscription (with caching for performance)
- Redirects to a configurable route if no active subscription exists

Configure the redirect route in `config/subscription-plans.php`:

```php
'middleware' => [
    'redirect_route' => env('SUBSCRIPTION_REDIRECT_ROUTE', 'subscription.plans'),
],
```

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

If you discover any security issues, please email hamzawemughales@gmail.com instead of using the issue tracker.

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

- 📧 Email: hamzawemughales@gmail.com
- 🐛 Issues: [GitHub Issues](https://github.com/hamza-mughales/laravel-subscriptions/issues)
- 💬 Discussions: [GitHub Discussions](https://github.com/hamza-mughales/laravel-subscriptions/discussions)
- 📖 Documentation: [GitHub Repository](https://github.com/Hamza-Mughales/subscription-plans)

