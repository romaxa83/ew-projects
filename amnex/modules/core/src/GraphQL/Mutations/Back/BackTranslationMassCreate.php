<?php

declare(strict_types=1);

namespace Wezom\Core\GraphQL\Mutations\Back;

use Illuminate\Validation\Rule;
use Wezom\Core\Dto\TranslationDto;
use Wezom\Core\Enums\TranslationSideEnum;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Models\Language;
use Wezom\Core\Models\Translation;
use Wezom\Core\Permissions\Ability;
use Wezom\Core\Services\TranslationService;

final class BackTranslationMassCreate extends BackFieldResolver
{
    protected bool $runInTransaction = true;

    public function __construct(private readonly TranslationService $translationService)
    {
    }

    public function resolve(Context $context): array
    {
        $items = collect($context->getArg('translations'))
            ->map(static fn (array $item) => TranslationDto::from($item));

        return $this->translationService->insertOrIgnore($items);
    }

    protected function rules(array $args = []): array
    {
        return [
            'translations' => ['required', 'array'],
            'translations.*' => ['required', 'array'],
            'translations.*.key' => ['required', 'string'],
            'translations.*.language' => ['required', 'string', Rule::exists(Language::class, 'slug')],
            'translations.*.text' => ['nullable', 'string'],
            'translations.*.side' => ['required', TranslationSideEnum::ruleIn()],
        ];
    }

    protected function ability(): Ability
    {
        return Ability::toModel(Translation::class)->createAction();
    }
}
