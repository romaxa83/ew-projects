<?php

namespace App\Enums\Favourites;

use Core\Enums\BaseEnum;

/**
 * Class FavouriteSubscriptionActionEnum
 * @package App\Enums\Favourites
 *
 * @method static static CREATED();
 * @method static static DELETED();
 * @method static static DELETED_ALL();
 */
class FavouriteSubscriptionActionEnum extends BaseEnum
{
    public const CREATED = 'created';
    public const DELETED = 'deleted';
    public const DELETED_ALL = 'deleted_all';
}
