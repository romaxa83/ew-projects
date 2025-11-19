<?php

namespace Wezom\Admins\GraphQL\Mutations\Back;

use Exception;
use Illuminate\Support\Facades\Auth;
use Wezom\Admins\Models\Admin;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Models\Auth\PersonalAccessToken;

final class BackAdminLogout extends BackFieldResolver
{
    public const NAME = 'backAdminLogout';

    public function resolve(Context $context): bool
    {
        try {
            /** @var Admin $admin */
            $admin = Auth::guard(Admin::GUARD)->user();

            /** @var PersonalAccessToken $hasAbilities */
            $hasAbilities = $admin->currentAccessToken();

            return (bool)$hasAbilities->delete();
        } catch (Exception) {
            return false;
        }
    }
}
