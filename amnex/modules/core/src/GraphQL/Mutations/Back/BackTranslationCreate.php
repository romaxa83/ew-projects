<?php

declare(strict_types=1);

namespace Wezom\Core\GraphQL\Mutations\Back;

use Wezom\Core\Dto\TranslationDto;
use Wezom\Core\GraphQL\BackFieldResolver;
use Wezom\Core\GraphQL\Context;
use Wezom\Core\Models\Translation;
use Wezom\Core\Permissions\Ability;
use Wezom\Core\Services\TranslationService;

final class BackTranslationCreate extends BackFieldResolver
{
    protected bool $runInTransaction = true;

    public function __construct(protected TranslationService $translationService)
    {
    }

    public function resolve(Context $context): Translation
    {
        $dto = $context->getDto(TranslationDto::class, 'translation');

        return $this->translationService->create($dto);
    }

    protected function rules(array $args = []): array
    {
        return $this->getDtoValidationRules(TranslationDto::class, $args, 'translation');
    }

    protected function ability(): Ability
    {
        return Ability::toModel(Translation::class)->createAction();
    }
}
