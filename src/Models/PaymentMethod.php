<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use HamzaMughales\Subscriptions\Database\Factories\PaymentMethodFactory;
use HamzaMughales\Subscriptions\Enums\PaymentMethodType;
use Spatie\Translatable\HasTranslations;

/**
 * PaymentMethod.
 *
 * @property int $id
 * @property array<string, string> $name
 * @property PaymentMethodType $type
 * @property bool $is_active
 * @property bool $is_default
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PaymentMethod extends Model
{
    /** @use HasFactory<PaymentMethodFactory> */
    use HasFactory, HasTranslations;

    /** @var array<int, string> */
    public $translatable = ['name'];

    protected $fillable = [
        'name',
        'type',
        'is_active',
        'is_default',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'type'       => PaymentMethodType::class,
        'is_active'  => 'boolean',
        'is_default' => 'boolean',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model): void {
            // If this payment method is being set as default, unset other defaults
            if ($model->is_default) {
                static::where('is_default', true)->update(['is_default' => false]);
            }
        });

        static::updating(function (self $model): void {
            // If this payment method is being set as default, unset other defaults
            if ($model->isDirty('is_default') && $model->is_default) {
                static::where('is_default', true)
                    ->where('id', '!=', $model->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->table = config('subscription-plans.table_names.payment_methods', 'plan_payment_methods');
    }
}
