<?php

namespace App\Foundations\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\LazyCollection;

/**
 * @mixin Model
 */
abstract class BaseModel extends Model
{
    public const DEFAULT_PER_PAGE = 10;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }
}

