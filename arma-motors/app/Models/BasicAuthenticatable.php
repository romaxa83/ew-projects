<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static static|Builder query()
 */
class BasicAuthenticatable extends \Illuminate\Foundation\Auth\User
{
    const GRAPHQL_SORT_ASC = 'ASC';
    const GRAPHQL_SORT_DESC = 'DESC';

    public function checkGraphqlSort(string $value): bool
    {
        return self::GRAPHQL_SORT_ASC === $value || self::GRAPHQL_SORT_DESC === $value;
    }
}
