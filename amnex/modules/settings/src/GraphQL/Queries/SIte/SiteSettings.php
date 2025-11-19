<?php

namespace Wezom\Settings\GraphQL\Queries\Site;

use Illuminate\Support\Collection;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Settings\Models\Setting;

class SiteSettings extends BaseFieldResolver
{
    public function resolve(Context $context): Collection
    {
        return Setting::query()
            ->whereIn('group_title', [
                Setting::GROUP_ACCESORIALS
            ])
            ->get()
        ;
    }
}
