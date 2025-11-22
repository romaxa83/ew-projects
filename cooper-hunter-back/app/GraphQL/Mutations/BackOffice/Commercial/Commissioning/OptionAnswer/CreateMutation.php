<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\OptionAnswer;

use App\Dto\Commercial\Commissioning\OptionAnswerDto;
use App\GraphQL\InputTypes\Commercial\Commissioning\OptionAnswerInput;
use App\GraphQL\Types\Commercial\Commissioning\OptionAnswerType;
use App\Models\Commercial\Commissioning\OptionAnswer;
use App\Models\Commercial\Commissioning\Question;
use App\Models\Localization\Language;
use App\Permissions\Commercial\Commissionings\Question\CreatePermission;
use App\Repositories\Commercial\Commissioning\QuestionRepository;
use App\Rules\TranslationsArrayValidator;
use App\Services\Commercial\Commissioning\OptionAnswerService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CreateMutation extends BaseMutation
{
    public const NAME = 'commissioningOptionAnswerCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(
        protected OptionAnswerService $service,
        protected QuestionRepository $questionRepository,
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'input' => OptionAnswerInput::nonNullType(),
        ];
    }

    public function type(): Type
    {
        return OptionAnswerType::nonNullType();
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
    ): OptionAnswer
    {
        $dto = OptionAnswerDto::byArgs($args['input']);

        /** @var $question Question */
        $question = $this->questionRepository->getByFields(['id' => $dto->questionId]);

        $model = makeTransaction(
            fn(): OptionAnswer => $this->service->create($dto, $question)
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


