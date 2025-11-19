<?php

namespace Wezom\Quotes\GraphQL\Mutations\Site;

use Exception;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Quotes\Dto\QuoteSiteAcceptDto;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Services\QuoteService;

class SiteQuoteAccept extends BaseFieldResolver
{
    protected bool $runInTransaction = true;
    protected array $dtoRulesMap = [
        'quote' => QuoteSiteAcceptDto::class,
    ];

    public function __construct(
        protected QuoteService $service,
    ) {
    }

    /**
     * @throws Exception
     */
    public function resolve(Context $context): Quote
    {
        $dto = $context->getDto(QuoteSiteAcceptDto::class, 'quote');

        $model = Quote::query()
            ->where('id', $dto->quoteId)
            ->firstOrFail();

        return $this->service->accept($model, $dto);
    }
}
