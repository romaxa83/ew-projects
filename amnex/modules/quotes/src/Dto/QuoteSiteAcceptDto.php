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
class QuoteSiteAcceptDto extends Data
{
    public function __construct(
        public readonly int $quoteId,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $userName,
    ) {
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'quoteId' => [
                'required',
                'integer',
                Rule::exists(Quote::TABLE, 'id')
                    ->where('status', QuoteStatusEnum::DRAFT)
            ],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'userName' => ['required', 'string', 'max:255'],
        ];
    }
}
