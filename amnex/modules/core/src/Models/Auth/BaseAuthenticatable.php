<?php

declare(strict_types=1);

namespace Wezom\Core\Models\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Sanctum\HasApiTokens;
use Wezom\Core\Traits\Permissions\HasRoles;
use Wezom\Core\Traits\SetPasswordTrait;

/**
 * @method static static|Builder query()
 * @method static static[]|Collection get()
 * @method static static[]|Collection|LengthAwarePaginator paginate(...$attr)
 *
 * @mixin Model
 */
abstract class BaseAuthenticatable extends User implements Authenticatable
{
    use HasApiTokens;
    use HasRoles;
    use Notifiable;
    use SetPasswordTrait;

    /** @return MorphMany<PersonalSession, $this> */
    public function sessions(): MorphMany
    {
        return $this->morphMany(PersonalSession::class, 'sessionable');
    }
}
