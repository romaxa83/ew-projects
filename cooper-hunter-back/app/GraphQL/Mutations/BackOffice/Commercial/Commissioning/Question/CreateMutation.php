<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Question;

use App\Dto\Commercial\Commissioning\QuestionDto;
use App\GraphQL\InputTypes\Commercial\Commissioning\QuestionInput;
use App\GraphQL\Types\Commercial\Commissioning\QuestionType;
use App\Models\Commercial\Commissioning\Question;
use App\Models\Localization\Language;
use App\Permissions\Commercial\Commissionings\Question\CreatePermission;
use App\Rules\TranslationsArrayValidator;
use App\Services\Commercial\Commissioning\QuestionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CreateMutation extends BaseMutation
{
    public const NAME = 'commissioningQuestionCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(protected QuestionService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'input' => QuestionInput::nonNullType(),
        ];
    }

    public function type(): Type
    {
        return QuestionType::nonNullType();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Question
    {
        $dto = QuestionDto::byArgs($args['input']);

        $model = makeTransaction(
            fn(): Question => $this->service->create($dto)
        );

        return $model;
    }

    protected function rules(array $args = []): array
    {
        return [
            'input.translations' => [new TranslationsArrayValidator()],
            'input.translations.*.language' => ['required', 'max:3', Rule::exists(Language::class, 'slug')],
            'input.translations.*.text' => ['required'],
        ];
    }
}


