<?php

namespace Wezom\Core\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Wezom\Core\Traits\Model\ActiveScopeTrait;

class ActiveGlobalScope implements Scope
{
    protected static bool $enabled = false;

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (static::$enabled) {
            /** @var $builder ActiveScopeTrait */
            /** @phpstan-ignore-next-line */
            $builder->active();
        }
    }

    public static function enable(): void
    {
        static::$enabled = true;
    }

    public static function disable(): void
    {
        static::$enabled = false;
    }
}
