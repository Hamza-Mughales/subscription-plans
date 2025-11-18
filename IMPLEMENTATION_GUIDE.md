# Implementation Guide - Laravel Subscription Plans

A step-by-step guide to implement this package in your Laravel projects.

---

## 📦 Step 1: Installation

### Install via Composer
```bash
composer require noot-web/subscription-plans
```

### Publish Assets
```bash
# Publish and run migrations
php artisan vendor:publish --tag=subscription-plans-migrations
php artisan migrate

# Publish configuration (optional)
php artisan vendor:publish --tag=subscription-plans-config

# Publish translations (optional)
php artisan vendor:publish --tag=subscription-plans-translations
```

---

## 👤 Step 2: Prepare Your Subscriber Model

### Add Interface and Trait

Any model can be a subscriber (User, Company, Team, etc.):

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use NootPro\SubscriptionPlans\Contracts\SubscriberInterface;
use NootPro\SubscriptionPlans\Traits\HasPlanSubscriptions;

class Company extends Model implements SubscriberInterface
{
    use HasPlanSubscriptions;
    
    // Your existing model code...
}
```

---

## 📋 Step 3: Create Plans

### Method 1: Via Seeder (Recommended)

Create a seeder:

```bash
php artisan make:seeder PlansSeeder
```

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use NootPro\SubscriptionPlans\Models\Plan;
use NootPro\SubscriptionPlans\Enums\{Features, Interval, PlanType, SubscriptionModel};

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        // Free Plan
        $freePlan = Plan::create([
            'name' => ['en' => 'Free Plan', 'ar' => 'الخطة المجانية'],
            'description' => ['en' => 'Perfect for getting started'],
            'slug' => 'free-plan',
            'price' => 0.00,
            'signup_fee' => 0.00,
            'currency' => 'USD',
            'trial_period' => 0,
            'trial_interval' => Interval::Day,
            'invoice_period' => 1,
            'invoice_interval' => Interval::Month,
            'grace_period' => 7,
            'grace_interval' => Interval::Day,
            'subscription_model' => SubscriptionModel::Fixed,
            'type' => PlanType::Plan,
            'is_active' => true,
            'is_visible' => true,
            'sort_order' => 1,
        ]);

        // Add features
        $freePlan->features()->create([
            'slug' => Features::Users->value,
            'name' => ['en' => 'Users', 'ar' => 'المستخدمين'],
            'value' => 1, // 1 user
            'resettable_period' => 0,
            'sort_order' => 1,
        ]);

        // Pro Plan
        $proPlan = Plan::create([
            'name' => ['en' => 'Pro Plan', 'ar' => 'الخطة الاحترافية'],
            'description' => ['en' => 'Best for growing businesses'],
            'slug' => 'pro-plan',
            'price' => 99.00,
            'signup_fee' => 0.00,
            'currency' => 'USD',
            'trial_period' => 14,
            'trial_interval' => Interval::Day,
            'invoice_period' => 1,
            'invoice_interval' => Interval::Month,
            'grace_period' => 7,
            'grace_interval' => Interval::Day,
            'subscription_model' => SubscriptionModel::Fixed,
            'type' => PlanType::Plan,
            'is_active' => true,
            'is_visible' => true,
            'sort_order' => 2,
        ]);

        $proPlan->features()->create([
            'slug' => Features::Users->value,
            'name' => ['en' => 'Users', 'ar' => 'المستخدمين'],
            'value' => 10, // 10 users
            'resettable_period' => 0,
            'sort_order' => 1,
        ]);

        // Enterprise Plan
        $enterprisePlan = Plan::create([
            'name' => ['en' => 'Enterprise Plan', 'ar' => 'خطة المؤسسات'],
            'description' => ['en' => 'For large organizations'],
            'slug' => 'enterprise-plan',
            'price' => 499.00,
            'signup_fee' => 0.00,
            'currency' => 'USD',
            'trial_period' => 30,
            'trial_interval' => Interval::Day,
            'invoice_period' => 1,
            'invoice_interval' => Interval::Month,
            'grace_period' => 14,
            'grace_interval' => Interval::Day,
            'subscription_model' => SubscriptionModel::Fixed,
            'type' => PlanType::Plan,
            'is_active' => true,
            'is_visible' => true,
            'sort_order' => 3,
        ]);

        $enterprisePlan->features()->create([
            'slug' => Features::Users->value,
            'name' => ['en' => 'Users', 'ar' => 'المستخدمين'],
            'value' => -1, // Unlimited users
            'resettable_period' => 0,
            'sort_order' => 1,
        ]);
    }
}
```

Run the seeder:

```bash
php artisan db:seed --class=PlansSeeder
```

---

## 🎯 Step 4: Create Subscriptions

### In Your Controller

```php
<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use NootPro\SubscriptionPlans\Models\Plan;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request, Company $company)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);
        
        $plan = Plan::findOrFail($validated['plan_id']);
        
        // Create subscription
        $subscription = $company->newPlanSubscription($plan);
        
        return redirect()->route('dashboard')
            ->with('success', 'Successfully subscribed to ' . $plan->name);
    }
    
    public function cancel(Company $company)
    {
        $subscription = $company->activePlanSubscription();
        
        if ($subscription) {
            // Cancel at end of period
            $subscription->cancel();
            
            // Or cancel immediately
            // $subscription->cancel(immediately: true);
            
            return redirect()->back()
                ->with('success', 'Subscription cancelled successfully');
        }
        
        return redirect()->back()
            ->with('error', 'No active subscription found');
    }
}
```

---

## 📊 Step 5: Track Usage

### Recording Feature Usage

```php
use NootPro\SubscriptionPlans\Enums\Features;

// Get active subscription
$subscription = $company->activePlanSubscription();

// Check if can use feature
if ($subscription->canUseFeature(Features::Users->value)) {
    // Create user
    $user = User::create([...]);
    
    // Record usage
    $subscription->recordFeatureUsage(Features::Users->value, 1);
} else {
    return back()->with('error', 'User limit reached. Please upgrade your plan.');
}
```

### Checking Usage

```php
// Get current usage
$used = $subscription->getFeatureUsage(Features::Users->value);

// Get remaining
$remaining = $subscription->getFeatureRemainings(Features::Users->value);

// Get feature value
$limit = $subscription->getFeatureValue(Features::Users->value);
// -1 = unlimited, 0 = disabled, >0 = limit
```

### Reducing Usage

```php
// When user is deleted
$subscription->reduceFeatureUsage(Features::Users->value, 1);

// Or
$subscription->decreaseUsage(Features::Users->value, 1);
```

---

## 🔍 Step 6: Check Subscription Status

### In Your Blade Views

```blade
@if($company->subscribedTo($plan->id))
    <span class="badge badge-success">Active</span>
@endif

@if($subscription->onTrial())
    <div class="alert alert-info">
        Trial ends in {{ $subscription->trial_ends_at->diffForHumans() }}
    </div>
@endif

@if($subscription->canceled())
    <div class="alert alert-warning">
        Subscription cancelled
    </div>
@endif
```

### In Your Controllers

```php
$subscription = $company->activePlanSubscription();

if ($subscription->active()) {
    // Subscription is active
}

if ($subscription->onTrial()) {
    // On trial period
}

if ($subscription->canceled()) {
    // Subscription was cancelled
}

if ($subscription->ended()) {
    // Subscription has ended
}
```

---

## 🔄 Step 7: Manage Subscriptions

### Change Plan

```php
$newPlan = Plan::where('slug', 'enterprise-plan')->first();
$subscription->changePlan($newPlan);
```

### Renew Subscription

```php
$subscription->renew();
```

### Cancel Subscription

```php
// Cancel at end of period
$subscription->cancel();

// Cancel immediately
$subscription->cancel(immediately: true);
```

---

## 🎨 Step 8: Display Plans (Example)

### Plans List Blade Component

```blade
<!-- resources/views/plans/index.blade.php -->
<div class="plans-grid">
    @foreach($plans as $plan)
        <div class="plan-card">
            <h3>{{ $plan->name }}</h3>
            <p class="price">
                @if($plan->isFree())
                    <span class="free">Free</span>
                @else
                    <span class="amount">${{ $plan->price }}</span>
                    <span class="period">/ {{ $plan->invoice_interval->value }}</span>
                @endif
            </p>
            
            @if($plan->hasTrial())
                <p class="trial">
                    {{ $plan->trial_period }} {{ $plan->trial_interval->value }} free trial
                </p>
            @endif
            
            <ul class="features">
                @foreach($plan->features as $feature)
                    <li>
                        @if($feature->value == -1)
                            Unlimited
                        @elseif($feature->value == 0)
                            <s>{{ $feature->name }}</s>
                        @else
                            {{ $feature->value }}
                        @endif
                        {{ $feature->name }}
                    </li>
                @endforeach
            </ul>
            
            @if(auth()->user()->company->subscribedTo($plan->id))
                <button class="btn btn-secondary" disabled>Current Plan</button>
            @else
                <form action="{{ route('subscribe') }}" method="POST">
                    @csrf
                    <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                    <button type="submit" class="btn btn-primary">
                        Subscribe Now
                    </button>
                </form>
            @endif
        </div>
    @endforeach
</div>
```

### Controller

```php
public function index()
{
    $plans = Plan::where('is_active', true)
        ->where('is_visible', true)
        ->with('features')
        ->orderBy('sort_order', 'asc')
        ->get();
    
    return view('plans.index', compact('plans'));
}
```

---

## 🔔 Step 9: Listen to Events

### Create Listener

```bash
php artisan make:listener SendSubscriptionNotification
```

```php
<?php

namespace App\Listeners;

use NootPro\SubscriptionPlans\Events\SubscriptionCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\SubscriptionStarted;

class SendSubscriptionNotification implements ShouldQueue
{
    public function handle(SubscriptionCreated $event): void
    {
        $subscription = $event->subscription;
        $subscriber = $subscription->subscriber;
        
        // Send notification
        $subscriber->notify(new SubscriptionStarted($subscription));
        
        // Log activity
        activity()
            ->performedOn($subscription)
            ->causedBy($subscriber)
            ->log('Subscription created');
    }
}
```

### Register in EventServiceProvider

```php
use NootPro\SubscriptionPlans\Events\{
    SubscriptionCreated,
    SubscriptionUpdated,
    SubscriptionDeleted,
    SubscriptionRestored,
};

protected $listen = [
    SubscriptionCreated::class => [
        SendSubscriptionNotification::class,
    ],
    SubscriptionUpdated::class => [
        // Your listeners
    ],
    SubscriptionDeleted::class => [
        // Your listeners
    ],
    SubscriptionRestored::class => [
        // Your listeners
    ],
];
```

---

## 🛡️ Step 10: Middleware Protection

### Create Middleware

```bash
php artisan make:middleware CheckSubscription
```

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, string $feature = null)
    {
        $company = auth()->user()->company;
        $subscription = $company->activePlanSubscription();
        
        // Check if has active subscription
        if (!$subscription || !$subscription->active()) {
            return redirect()->route('plans.index')
                ->with('error', 'Please subscribe to a plan to access this feature.');
        }
        
        // Check specific feature if provided
        if ($feature && !$subscription->canUseFeature($feature)) {
            return redirect()->back()
                ->with('error', 'Your current plan does not support this feature.');
        }
        
        return $next($request);
    }
}
```

### Register Middleware

```php
// app/Http/Kernel.php
protected $middlewareAliases = [
    'subscribed' => \App\Http\Middleware\CheckSubscription::class,
];
```

### Use in Routes

```php
// Require active subscription
Route::middleware(['auth', 'subscribed'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Require specific feature
Route::middleware(['auth', 'subscribed:users'])->group(function () {
    Route::resource('users', UserController::class);
});
```

---

## 📅 Step 11: Scheduled Tasks

### Handle Expired Subscriptions

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule): void
{
    // Check for expired trials
    $schedule->call(function () {
        \NootPro\SubscriptionPlans\Models\PlanSubscription::findEndedTrial()
            ->get()
            ->each(function ($subscription) {
                // Handle expired trial
                $subscription->subscriber->notify(
                    new TrialExpiredNotification($subscription)
                );
            });
    })->daily();
    
    // Check for expiring subscriptions
    $schedule->call(function () {
        \NootPro\SubscriptionPlans\Models\PlanSubscription::findEndingPeriod(7)
            ->get()
            ->each(function ($subscription) {
                // Notify about expiring subscription
                $subscription->subscriber->notify(
                    new SubscriptionExpiringNotification($subscription)
                );
            });
    })->daily();
}
```

---

## 🎯 Common Use Cases

### 1. Feature-Based Access Control

```php
// In your controller
public function createProject(Request $request)
{
    $company = auth()->user()->company;
    $subscription = $company->activePlanSubscription();
    
    if (!$subscription->canUseFeature('projects')) {
        return response()->json([
            'error' => 'Project limit reached'
        ], 403);
    }
    
    $project = Project::create($request->validated());
    $subscription->recordFeatureUsage('projects');
    
    return response()->json($project, 201);
}
```

### 2. Blade Directive (Custom)

```php
// In AppServiceProvider
use Illuminate\Support\Facades\Blade;

public function boot(): void
{
    Blade::if('subscribed', function ($feature = null) {
        $company = auth()->user()->company;
        $subscription = $company->activePlanSubscription();
        
        if (!$subscription || !$subscription->active()) {
            return false;
        }
        
        if ($feature) {
            return $subscription->canUseFeature($feature);
        }
        
        return true;
    });
}
```

Usage:

```blade
@subscribed
    <a href="{{ route('premium.feature') }}">Premium Feature</a>
@else
    <a href="{{ route('plans.index') }}">Upgrade to Access</a>
@endsubscribed

@subscribed('projects')
    <button>Create Project</button>
@endsubscribed
```

---

## 🧪 Writing Tests with PEST

This package uses PEST for testing. Here's how to write tests for your implementation:

```php
<?php

use App\Models\Company;
use NootPro\SubscriptionPlans\Models\Plan;

it('can subscribe a company to a plan', function () {
    $company = Company::factory()->create();
    $plan = Plan::factory()->create(['price' => 99.00]);

    $subscription = $company->newPlanSubscription($plan);

    expect($subscription)
        ->toBeActive()
        ->plan_id->toBe($plan->id);
});

it('tracks feature usage correctly', function () {
    $company = Company::factory()->create();
    $plan = Plan::factory()->create();
    $plan->features()->create([
        'slug' => 'users',
        'value' => 5,
    ]);

    $subscription = $company->newPlanSubscription($plan);

    expect($subscription->canUseFeature('users'))->toBeTrue();
    
    $subscription->recordFeatureUsage('users', 1);
    
    expect($subscription->getFeatureUsage('users'))->toBe(1);
    expect($subscription->getFeatureRemainings('users'))->toBe(4);
});
```

Run your tests:

```bash
composer test
composer test-coverage
```

---

## 🚀 Production Checklist

- [ ] Plans created and configured
- [ ] Subscriber models prepared
- [ ] Controllers implemented
- [ ] Event listeners registered
- [ ] Middleware configured
- [ ] Scheduled tasks set up
- [ ] Views created
- [ ] Tests written with PEST
- [ ] Payment gateway integrated (if needed)
- [ ] Email notifications configured
- [ ] Error handling implemented
- [ ] Logging configured

---

## 📝 Tips & Best Practices

1. **Always check subscription status** before allowing access to premium features
2. **Use events** for logging, notifications, and analytics
3. **Implement grace periods** to avoid abrupt service interruption
4. **Cache plan data** if it doesn't change frequently
5. **Use middleware** for route-level subscription checks
6. **Set up monitoring** for subscription metrics
7. **Test subscription lifecycle** thoroughly
8. **Document your custom features** clearly

---

## 🆘 Troubleshooting

### Subscription not activating
- Check if `is_active` is set to `true`
- Verify dates are correct (starts_at, ends_at)
- Ensure no other active subscription exists

### Feature usage not tracking
- Verify feature slug matches exactly
- Check if feature exists in plan
- Ensure `recordFeatureUsage()` is called after action

### Performance issues
- Add database indexes (already included in package)
- Cache frequently accessed plan data
- Use eager loading: `$plans->load('features')`

---

## 📞 Support

Need help? Check:
- [README.md](README.md) - Full documentation
- [IMPROVEMENTS.md](IMPROVEMENTS.md) - Technical details
- [GitHub Issues](https://github.com/noot-web/subscription-plans/issues)

---

**Happy Coding! 🎉**

