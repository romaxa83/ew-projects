<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @method static static|Builder query()
 * @method static static[]|Collection get()
 * @method static static[]|Collection|LengthAwarePaginator paginate(...$attr)
 *
 * @mixin Model
 */
class BasicAuthenticatable extends User
{

}
