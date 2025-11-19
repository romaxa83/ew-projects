<?php

namespace Wezom\Core\Traits;

use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Support\Validation\ValidationContext;

trait SlugValidationRules
{
    /**
     * @return class-string
     */
    abstract protected static function model(): string;

    public static function rules(ValidationContext $context): array
    {
        return [
            'slug' => [
                new Required(),
                new StringType(),
                new Max(254),
                new Unique(
                    static::model(),
                    'slug',
                    ignore: $context->fullPayload['id'] ?? null,
                    ignoreColumn: 'row_id'
                ),
            ],
        ];
    }
}
