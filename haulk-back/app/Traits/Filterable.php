<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static static|Builder filter($attributes = [], $filterClass = null)
 */
trait Filterable
{
    use \EloquentFilter\Filterable;

    abstract public function modelFilter(): string;
}
