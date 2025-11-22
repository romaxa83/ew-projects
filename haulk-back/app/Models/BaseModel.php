<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @method static Builder|static query()
 * @method static Collection|static[] get()
 * @method static Collection|LengthAwarePaginator|static[] paginate(int $perPage, $columns = ['*'], $name = 'page', $page = null)
 */
abstract class BaseModel extends Model
{

}
