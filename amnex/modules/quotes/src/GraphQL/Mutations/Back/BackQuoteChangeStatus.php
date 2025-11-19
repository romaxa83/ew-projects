<?php

namespace Wezom\Quotes\GraphQL\Mutations\Site;

use Exception;
use Illuminate\Validation\Rule;
use Wezom\Core\GraphQL\BaseFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Quotes\Enums\QuoteStatusEnum;
use Wezom\Quotes\Models\Quote;
use Wezom\Quotes\Services\QuoteStatusService;

class BackQuoteChangeStatus extends BaseFieldResolver
{
    protected bool $runInTransaction = true;

    /**
     * @throws Exception
     */
    public function resolve(Context $context)
    {
        $model = Quote::query()
            ->where('id', $context->getArgs()['quote']['quoteId'])
            ->firstOrFail();

        return QuoteStatusService::setStatus($model, $context->getArgs()['quote']['status']);
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
            'quote.status' => [
                'required',
            ],
        ];
    }
}
