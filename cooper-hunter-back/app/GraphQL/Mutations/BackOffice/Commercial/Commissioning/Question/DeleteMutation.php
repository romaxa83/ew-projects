<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Commissioning\Question;

use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\Question;
use App\Permissions\Commercial\Commissionings\Question\DeletePermission;
use App\Repositories\Commercial\Commissioning\QuestionRepository;
use App\Services\Commercial\Commissioning\QuestionService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class DeleteMutation extends BaseMutation
{
    public const NAME = 'commissioningQuestionDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(
        protected QuestionService $service,
        protected QuestionRepository $repo,
    )
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
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
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool
    {
        return makeTransaction(
            fn() => $this->service->delete(
                $this->repo->getByFields(['id' => $args['id']])
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'int', Rule::exists(Question::TABLE, 'id')],
        ];
    }
}


