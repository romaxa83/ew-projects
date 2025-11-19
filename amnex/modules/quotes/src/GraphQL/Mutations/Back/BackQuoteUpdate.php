<?php

namespace Wezom\Quotes\GraphQL\Mutations\Site;

use Exception;
use Illuminate\Validation\Rule;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Quotes\Dto\QuoteBackDto;
use Wezom\Quotes\Enums\QuoteStatusEnum;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Services\QuoteService;

class BackQuoteUpdate extends BaseFieldResolver
{
    protected bool $runInTransaction = true;
    protected array $dtoRulesMap = [
        'quote' => QuoteBackDto::class,
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
        $dto = $context->getDto(QuoteBackDto::class, 'quote');

        $model = Quote::query()
            ->where('id', $dto->quoteId)
            ->firstOrFail();

        return $this->service->updateBack($model, $dto);
    }

    protected function rules(array $args = []): array
    {
        return [
            'quote.quoteId' => [
                'required',
                'integer',
                Rule::exists(Quote::TABLE, 'id')
                    ->whereNotIn('status', [
                        QuoteStatusEnum::DRAFT
                    ])
            ],
            'quote.containerNumber' => [
                'nullable',
                'string',
                'max:200',
                Rule::unique(Quote::TABLE, 'container_number')
                    ->ignore($args['quote']['quoteId'] ?? null)
            ],
            'quote.mileageCost' => ['nullable', 'numeric'],
            'quote.cargoCost' => ['nullable', 'numeric'],
            'quote.storageCost' => ['nullable', 'numeric'],
        ];
    }
}
