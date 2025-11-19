<?php

declare(strict_types=1);

namespace Wezom\Core\GraphQL\Queries\Site;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Wezom\Core\Enums\TranslationSideEnum;
use Wezom\Core\GraphQL\BaseQuery;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Models\Translation;

class SiteTranslations extends BaseQuery
{
    public const NAME = 'siteTranslations';

    public function resolve(Context $context): Collection
    {
        $key = sprintf('translations_all_%s_%s', implode('-', $context->getArg('side')), $context->getArg('language'));

        return Cache::tags('translations')
            ->rememberForever($key, function () use ($context) {
                return Translation::query()
                    ->filter($context->getArgs())
                    ->get();
            });
    }

    protected function rules(array $args = []): array
    {
        return [
            'language' => ['required', 'string', 'exists:languages,slug'],
            'side' => ['required', 'array'],
            'side.*' => ['required', TranslationSideEnum::ruleIn()],
        ];
    }
}
