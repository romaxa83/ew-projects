<?php

namespace WezomCms\Core\Traits\Model;

use WezomCms\Core\Scopes\GetForSelectScope;

/**
 * Trait GetForSelectTrait
 * @method static array getForSelect(bool $placeholder = false, callable|string|string[] $value = 'name', string|string[] $key = 'id')
 */
trait GetForSelectTrait
{
    /**
     * Boot the trait for a model.
     *
     * @return void
     */
    protected static function bootGetForSelectTrait()
    {
        static::addGlobalScope(new GetForSelectScope());
    }
}
