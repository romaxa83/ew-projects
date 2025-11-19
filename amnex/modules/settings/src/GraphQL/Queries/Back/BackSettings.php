<?php

namespace Wezom\Settings\GraphQL\Queries\Back;

use Illuminate\Support\Collection;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Settings\Models\Setting;

class BackSettings extends BackFieldResolver
{
    public function resolve(Context $context): Collection
    {
        return Setting::query()
            ->get()
        ;
    }
}
