<?php

namespace App\GraphQL\Mutations\BackOffice\Faq\Questions;

use App\GraphQL\Types\NonNullType;
use App\Models\Faq\Question;
use App\Permissions\Faq\Questions\QuestionDeletePermission;
use App\Services\Faq\QuestionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class QuestionDeleteMutation extends BaseMutation
{
    public const NAME = 'questionDelete';
    public const PERMISSION = QuestionDeletePermission::KEY;

    public function __construct(protected QuestionService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'question_id' => [
                'type' => NonNullType::id(),
                'rules' => [Rule::exists(Question::class, 'id')],
            ],
        ];
    }


    public function type(): Type
    {
        return Type::boolean();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(Question::query()->find($args['question_id']))
        );
    }
}
