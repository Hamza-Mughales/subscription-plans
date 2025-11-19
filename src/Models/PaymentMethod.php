<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * PaymentMethod.
 *
 * @property string $name
 * @property string $slug
 * @property bool $is_active
 * @property bool $is_default
 */
class PaymentMethod extends Model
{
    /** @use HasFactory<\Illuminate\Database\Eloquent\Factories\Factory> */
    use HasFactory, HasTranslations;

    /** @var array<int, string> */
    public array $translatable = ['name'];

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('subscription-plans.table_names.payment_methods', 'plan_payment_methods');
    }
}
