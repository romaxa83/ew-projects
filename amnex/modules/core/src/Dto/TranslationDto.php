<?php

namespace Wezom\Core\Dto;

use GraphQL\Type\Definition\Description;
use Illuminate\Database\Query\Builder;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Support\Validation\ValidationContext;
use Wezom\Core\Enums\TranslationSideEnum;
use Wezom\Core\Models\Translation;
use Wezom\Core\Rules\TranslationLocaleRule;

#[MapOutputName(SnakeCaseMapper::class)]
class TranslationDto extends Data
{
    public function __construct(
        #[Description('example - validation.first_name')]
        public readonly string $key,
        #[Description('(en)')]
        public readonly string $language,
        public readonly ?string $text,
        public readonly TranslationSideEnum $side
    ) {
    }

    public static function rules(ValidationContext $context): array
    {
        return [
            'key' => [
                new Required(),
                new StringType(),
                new Unique(
                    Translation::class,
                    'key',
                    ignore: $context->fullPayload['id'] ?? null,
                    where: function (Builder $builder) use ($context) {
                        $builder->where(
                            'side',
                            $context->fullPayload['side'] ? (string)$context->fullPayload['side'] : null
                        )
                            ->where('language', $context->fullPayload['language'] ?? null);
                    }
                ),
            ],
            'language' => [
                new Required(),
                new StringType(),
                new TranslationLocaleRule(),
            ],
        ];
    }
}
