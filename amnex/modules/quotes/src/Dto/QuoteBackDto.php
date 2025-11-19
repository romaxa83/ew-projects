<?php

declare(strict_types=1);

namespace Wezom\Quotes\Dto;

use Illuminate\Validation\Rule;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Wezom\Quotes\Enums\QuoteStatusEnum;
use Wezom\Quotes\Models\Quote;

#[MapOutputName(SnakeCaseMapper::class)]
class QuoteBackDto extends Data
{
    public function __construct(
        public readonly int $quoteId,
        public readonly ?string $containerNumber,
        public readonly ?float $mileageCost,
        public readonly ?float $cargoCost,
        public readonly ?float $storageCost,
    ) {
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'quoteId' => [
                'required',
                'integer',
                Rule::exists(Quote::TABLE, 'id')
                    ->whereNotIn('status', [
                        QuoteStatusEnum::DRAFT
                    ])
            ],
            'containerNumber' => [
                'nullable',
                'string',
                'max:200',
//                Rule::unique(Quote::TABLE, 'container_number')
            ],
            'mileageCost' => ['nullable', 'numeric'],
            'cargoCost' => ['nullable', 'numeric'],
            'storageCost' => ['nullable', 'numeric'],
        ];
    }
}
