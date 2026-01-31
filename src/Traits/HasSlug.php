<?php

declare(strict_types=1);

namespace HamzaMughales\Subscriptions\Traits;

use Spatie\Sluggable\HasSlug as BaseHasSlug;
use Spatie\Sluggable\SlugOptions;

trait HasSlug
{
    use BaseHasSlug;

    protected static function bootHasSlug(): void
    {
        // Auto generate slugs early before validation
        static::creating(function (self $model): void {
            if ($model->exists && $model->getSlugOptions()->generateSlugsOnUpdate) {
                $model->generateSlugOnUpdate();
            } elseif (! $model->exists && $model->getSlugOptions()->generateSlugsOnCreate) {
                $model->generateSlugOnCreate();
            }
        });
    }

    abstract public function getSlugOptions(): SlugOptions;
}
