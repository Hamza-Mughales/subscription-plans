<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use NootPro\SubscriptionPlans\Enums\Modules;
use NootPro\SubscriptionPlans\Facades\SubscriptionPlans;

/**
 * ModulesGate Trait
 *
 * This trait provides authorization methods for Filament resources and pages
 * based on active subscription modules. It checks if the current tenant/company
 * has the required modules enabled before allowing access.
 *
 * @example
 * class WebsiteResource extends Resource
 * {
 *     use ModulesGate;
 *
 *     protected static function getModuleNames(): array|string
 *     {
 *         return Modules::WebsiteContent->value;
 *     }
 * }
 */
trait ModulesGate
{
    /**
     * Get the module names that this resource/page requires.
     * Override this method in your resource/page class.
     */
    protected static function getModuleNames(): array|string
    {
        return Modules::WebsiteContent->value;
    }

    /**
     * Get the tenant model class.
     * Override this method if your tenant model is different.
     */
    protected static function getTenantModelClass(): ?string
    {
        // Try to get from config first
        $tenantModel = config('subscription-plans.tenant_model');

        if ($tenantModel) {
            return $tenantModel;
        }

        // Default fallback - try common tenant model names
        if (class_exists(\App\Models\Company::class)) {
            return \App\Models\Company::class;
        }

        return null;
    }

    /**
     * Check if any of the required modules are active for the current tenant.
     */
    protected static function hasAnyActiveModule(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Get current company/tenant
        $tenant = null;
        if (class_exists(\Filament\Facades\Filament::class)) {
            $tenant = \Filament\Facades\Filament::getTenant();
        }

        $tenantModelClass = static::getTenantModelClass();
        if (! $tenant && $tenantModelClass) {
            // Try to resolve tenant from user if Filament is not available
            if (method_exists($user, 'company')) {
                $tenant = $user->company;
            } elseif (method_exists($user, 'tenant')) {
                $tenant = $user->tenant;
            }
        }

        if (! $tenant) {
            return false;
        }

        // Verify tenant is instance of expected model
        if ($tenantModelClass && ! ($tenant instanceof $tenantModelClass)) {
            return false;
        }

        $modules = (array) static::getModuleNames();

        return collect($modules)->contains(function ($module) use ($tenant) {
            return SubscriptionPlans::moduleEnabled($tenant, $module);
        });
    }

    /**
     * Determine if the navigation item should be registered.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::hasAnyActiveModule();
    }

    /**
     * Determine if the user can view any records.
     */
    public static function canViewAny(): bool
    {
        return static::hasAnyActiveModule();
    }

    /**
     * Determine if the user can access the resource/page.
     */
    public static function canAccess(): bool
    {
        return static::hasAnyActiveModule();
    }

    /**
     * Determine if the user can create records.
     */
    public static function canCreate(): bool
    {
        return static::hasAnyActiveModule();
    }

    /**
     * Determine if the user can update the record.
     */
    public static function canUpdate(): bool
    {
        return static::hasAnyActiveModule();
    }

    /**
     * Determine if the user can delete the record.
     */
    public static function canDelete(Model $record): bool
    {
        return static::hasAnyActiveModule();
    }

    /**
     * Determine if the user can delete any records.
     */
    public static function canDeleteAny(): bool
    {
        return static::hasAnyActiveModule();
    }

    /**
     * Determine if the user can restore the record.
     */
    public static function canRestore(Model $record): bool
    {
        return static::hasAnyActiveModule();
    }

    /**
     * Determine if the user can force delete the record.
     */
    public static function canForceDelete(Model $record): bool
    {
        return static::hasAnyActiveModule();
    }

    /**
     * Determine if the user can view the record.
     */
    public static function canView(Model $record): bool
    {
        return static::hasAnyActiveModule();
    }
}
