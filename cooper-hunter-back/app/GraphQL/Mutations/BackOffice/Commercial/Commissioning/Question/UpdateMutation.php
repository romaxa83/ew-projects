<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Question;

use App\Dto\Commercial\Commissioning\QuestionDto;
use App\GraphQL\InputTypes\Commercial\Commissioning\QuestionUpdateInput;
use App\GraphQL\Types\Commercial\Commissioning\QuestionType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\Question;
use App\Models\Localization\Language;
use App\Permissions\Commercial\Commissionings\Question\UpdatePermission;
use App\Repositories\Commercial\Commissioning\QuestionRepository;
use App\Rules\TranslationsArrayValidator;
use App\Services\Commercial\Commissioning\ProjectProtocolQuestionService;
use App\Services\Commercial\Commissioning\QuestionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class UpdateMutation extends BaseMutation
{
    public const NAME = 'commissioningQuestionUpdate';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected QuestionService $service,
        protected QuestionRepository $repo,
        protected ProjectProtocolQuestionService $projectProtocolQuestionService
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => ['required', 'int', Rule::exists(Question::class, 'id')],
            ],
            'input' => QuestionUpdateInput::nonNullType(),
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
        /** @var $model Question */
        $model = $this->repo->getByFields(['id' => $args['id']]);

        $model = makeTransaction(
            fn(): Question => $this->service->update($model, $dto)
        );

        if($model->status->isActive()){
            $this->projectProtocolQuestionService
                ->attachQuestionToProjectProtocol($model);
        }
        if($model->status->isInactive()){
            $this->projectProtocolQuestionService
                ->detachQuestionFromProjectProtocol($model);
        }

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

