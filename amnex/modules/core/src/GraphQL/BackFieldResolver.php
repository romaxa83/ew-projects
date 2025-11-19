<?php

namespace Wezom\Core\GraphQL;

use Wezom\Admins\Models\Admin;

abstract class BackFieldResolver extends BaseFieldResolver
{
    protected function guards(): array|string
    {
        return Admin::GUARD;
    }
}
