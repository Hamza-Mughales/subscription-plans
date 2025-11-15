<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Enums;

/**
 * Modules Enum
 *
 * This enum defines the available modules that can be assigned to subscription plans.
 * Since modules vary from project to project, you should customize this enum to match
 * your application's specific modules.
 *
 * To customize:
 * 1. Create your own Modules enum in your application (e.g., App\Enums\Modules)
 * 2. Update config/subscription-plans.php to point to your custom enum:
 *    'enums' => [
 *        'modules' => \App\Enums\Modules::class,
 *    ]
 *
 * @example
 * enum Modules: string
 * {
 *     case ECommerce = 'ecommerce';
 *     case Blog = 'blog';
 *     case Analytics = 'analytics';
 *     // Add your project-specific modules here
 * }
 */
enum Modules: string
{
    /**
     * Example module cases - customize these for your project.
     * These are placeholder values and should be replaced with your actual modules.
     */
    case WebsiteContent = 'website_content';
    case Catalog = 'catalog';

    /**
     * Get the display label for the module.
     * Compatible with Filament's HasLabel interface if Filament is installed.
     */
    public function getLabel(): string
    {
        return __('subscription-plans::subscription-plans.modules.'.$this->value);
    }
}
