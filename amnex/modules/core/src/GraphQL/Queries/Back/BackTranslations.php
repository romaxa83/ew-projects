<?php

declare(strict_types=1);

namespace Wezom\Core\GraphQL\Queries\Back;

use Illuminate\Support\Collection;
use Wezom\Core\Enums\TranslationSideEnum;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Models\Translation;
use Wezom\Core\Permissions\Ability;

class BackTranslations extends BackFieldResolver
{
    public const NAME = 'backTranslations';

    public function resolve(Context $context): Collection
    {
        return Translation::query()
            ->filter($context->getArgs())
            ->get();
    }

    protected function rules(array $args = []): array
    {
        return [
            'language' => ['nullable', 'string', 'exists:languages,slug'],
            'side' => ['array'],
            'side.*' => ['required', TranslationSideEnum::ruleIn()],
            'key' => ['nullable', 'string'],
        ];
    }

    protected function ability(): Ability
    {
        return Ability::toModel(Translation::class)->viewAction();
    }
}
