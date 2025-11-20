<?php

namespace App\Models;

use App\ValueObjects\Email;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Laravel\Passport\HasApiTokens;

/**
 * @method static static|Builder query()
 * @method static static[]|Collection get()
 * @method static static[]|Collection|LengthAwarePaginator paginate(...$attr)
 *
 * @mixin Model
 */
abstract class BaseAuthenticatable extends User
{
    use HasApiTokens;

    abstract public function getName(): string;
    abstract public function getEmail(): Email;
    abstract public function getEmailVerificationCode(): ?string;
}
