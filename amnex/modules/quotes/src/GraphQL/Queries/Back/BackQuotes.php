<?php

namespace Wezom\Quotes\GraphQL\Queries\Back;

use Illuminate\Database\Eloquent\Builder;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Quotes\Enums\QuoteStatusEnum;
use Wezom\Quotes\Models\Quote;

class BackQuotes extends BackFieldResolver
{
    public function resolve(Context $context): Builder
    {
        $filter = $context->getArgs();

        return Quote::query()
            ->whereNotIn('status', [
                QuoteStatusEnum::DRAFT
            ])
            ->filter($filter)
            ->orderBy('quote_accepted_at', 'desc')
        ;
    }
}
