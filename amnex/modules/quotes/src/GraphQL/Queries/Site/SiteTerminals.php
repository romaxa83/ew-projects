<?php

namespace Wezom\Quotes\GraphQL\Queries\Site;

use Illuminate\Support\Collection;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Quotes\Models\Terminal;

class SiteTerminals extends BaseFieldResolver
{
    public function resolve(Context $context): Collection
    {
        return Terminal::query()->get();
    }
}
