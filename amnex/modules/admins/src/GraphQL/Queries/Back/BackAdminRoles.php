<?php

declare(strict_types=1);

namespace Wezom\Admins\GraphQL\Queries\Back;

use Illuminate\Database\Eloquent\Builder;
use Wezom\Admins\Models\Admin;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Models\Permission\Role;
use Wezom\Core\Permissions\Ability;

class BackAdminRoles extends BackFieldResolver
{
    public function resolve(Context $context): Builder
    {
        return Role::where('guard_name', Admin::GUARD)
            ->filter($context->getArgs());
    }

    protected function ability(): Ability
    {
        return Ability::toModel(Role::class)->viewAction();
    }
}
