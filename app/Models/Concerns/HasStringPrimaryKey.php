<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasStringPrimaryKey
{
    /**
     * Boot the trait.
     */
    protected static function bootHasStringPrimaryKey(): void
    {
        static::creating(function ($model): void {
            if (empty($model->id)) {
                $model->id = (string) Str::ulid();
            }
        });
    }

    /**
     * Get the auto-incrementing key type.
     */
    public function getKeyType(): string
    {
        return 'string';
    }

    /**
     * Indicates if the IDs are auto-incrementing.
     */
    public function getIncrementing(): bool
    {
        return false;
    }
}
