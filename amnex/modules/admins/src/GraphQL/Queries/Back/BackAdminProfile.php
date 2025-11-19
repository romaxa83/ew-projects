<?php

declare(strict_types=1);

namespace Wezom\Admins\GraphQL\Queries\Back;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Wezom\Admins\Models\Admin as AdminModel;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;

class BackAdminProfile extends BackFieldResolver
{
    public const NAME = 'backAdminProfile';

    public function resolve(Context $context): Authenticatable
    {
        return Auth::guard(AdminModel::GUARD)->user();
    }
}
