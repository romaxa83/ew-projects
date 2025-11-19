<?php

namespace Wezom\Core\Traits;

use Exception;
use Illuminate\Support\Str;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\StringType;
use Spatie\LaravelData\Attributes\Validation\Unique;
use Spatie\LaravelData\Support\Validation\ValidationContext;

trait SlugLanguageValidationRules
{
    /**
     * @throws Exception
     */
    public static function rules(ValidationContext $context): array
    {
        return [
            'slug' => static::slugRules($context),
        ];
    }

    /**
     * @throws Exception
     */
    protected static function slugRules(ValidationContext $context): array
    {
        return [
            new Required(),
            new StringType(),
            new Max(254),
            static::slugUniqueRule($context),
        ];
    }

    /**
     * @throws Exception
     */
    protected static function slugUniqueRule(ValidationContext $context): Unique
    {
        $contextPath = $context->path->get();
        $path = Str::replace(Str::before($contextPath, 'translation'), '', $contextPath);
        $currentTranslation = data_get($context->fullPayload, $path);

        return new Unique(
            self::model(),
            'slug',
            ignore: $context->fullPayload['id'] ?? null,
            ignoreColumn: 'row_id',
            where: fn ($query) => $query->where('language', $currentTranslation['language'])
        );
    }

    /**
     * @return class-string
     */
    abstract protected static function model(): string;
}
