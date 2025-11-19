<?php

namespace Wezom\Quotes\GraphQL\Queries\Back;

use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Quotes\Services\QuoteAnalyticService;

class BackQuotesAnalyticData extends BackFieldResolver
{
    public function __construct(
        protected QuoteAnalyticService $service,
    ) {
    }

    public function resolve(Context $context): array
    {
        $filter = $context->getArgs();

        return $this->service->simpleData($filter);
    }
}
