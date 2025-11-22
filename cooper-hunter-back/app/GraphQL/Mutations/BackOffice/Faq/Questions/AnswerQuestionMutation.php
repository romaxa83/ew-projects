<?php

namespace App\GraphQL\Mutations\BackOffice\Faq\Questions;

use App\Dto\Faq\Questions\AnswerQuestionDto;
use App\GraphQL\InputTypes\Faq\Questions\AnswerQuestionInput;
use App\GraphQL\Types\Faq\QuestionType;
use App\GraphQL\Types\NonNullType;
use App\Models\Faq\Question;
use App\Permissions\Faq\Questions\QuestionAnswerPermission;
use App\Services\Faq\QuestionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class AnswerQuestionMutation extends BaseMutation
{
    public const NAME = 'answerQuestion';
    public const PERMISSION = QuestionAnswerPermission::KEY;

    public function __construct(protected QuestionService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return QuestionType::nonNullType();
    }

    public function args(): array
    {
        return [
            'question_id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(Question::class, 'id')],
            ],
            'input' => [
                'type' => AnswerQuestionInput::nonNullType(),
            ],
        ];
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
    ): Question {
        return makeTransaction(
            fn() => $this->service->answer(
                $this->user(),
                Question::query()->find($args['question_id']),
                AnswerQuestionDto::byArgs($args['input'])
            )
        );
    }
}
