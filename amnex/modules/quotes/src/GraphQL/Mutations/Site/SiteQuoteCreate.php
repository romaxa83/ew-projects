<?php

namespace Wezom\Quotes\GraphQL\Mutations\Site;

use Exception;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Quotes\Dto\QuoteSiteDto;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Services\QuoteService;

class SiteQuoteCreate extends BaseFieldResolver
{
    protected bool $runInTransaction = true;
    protected array $dtoRulesMap = [
        'quote' => QuoteSiteDto::class,
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
        $dto = $context->getDto(QuoteSiteDto::class, 'quote');

        $model = $this->service->create($dto);

        if (!$model->is_not_standard_dimension) {
            $model = $this->service->calculationAndSet($model);
        }

        return $model;
    }
}
