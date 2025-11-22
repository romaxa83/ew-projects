<?php

namespace App\GraphQL\Queries\BackOffice\Commercial\Commissioning;

use App\GraphQL\Types\Commercial\Commissioning\QuestionType;
use App\GraphQL\Types\Enums\Commercial\Commissioning\AnswerTypeEnumType;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\Commissioning\Protocol;
use App\Permissions\Commercial\Commissionings\Question\ListPermission;
use App\Repositories\Commercial\Commissioning\QuestionRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class QuestionQuery extends BaseQuery
{
    public const NAME = 'commissioningQuestions';
    public const PERMISSION = ListPermission::KEY;

    public function __construct(protected QuestionRepository $repo)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        $parent = parent::args();
        unset(
            $parent['created_at'],
            $parent['updated_at'],
        );

        return array_merge(
            $parent,
            [
                'protocol_id' => [
                    'type' => Type::id(),
                    ['required', 'int', Rule::exists(Protocol::class, 'id')]
                ],
                'answer_type' => [
                    'type' => AnswerTypeEnumType::type(),
                    'description' => 'Filter by answer type',
                ],
            ]
        );
    }

    public function type(): Type
    {
        return QuestionType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator
    {
        return $this->repo->getAllPagination(
            $fields->getRelations(),
            $args,
            'sort'
        );
    }
}


