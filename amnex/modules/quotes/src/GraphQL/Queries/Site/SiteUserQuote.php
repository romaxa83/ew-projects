<?php

namespace Wezom\Quotes\GraphQL\Queries\Site;

use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Quotes\Models\Quote;

class SiteUserQuote extends BaseFieldResolver
{
    public function resolve(Context $context): ?Quote
    {
        return Quote::query()
            ->where('container_number', $context->getArgs()['containerNumber'])
            ->first()
        ;
    }
}
