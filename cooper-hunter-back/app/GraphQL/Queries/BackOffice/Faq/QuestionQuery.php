<?php

namespace App\GraphQL\Queries\BackOffice\Faq;

use App\GraphQL\Types\Enums\Faq\Questions\QuestionStatusEnumType;
use App\GraphQL\Types\Faq\QuestionType;
use App\Models\Faq\Question;
use App\Permissions\Faq\Questions\QuestionListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class QuestionQuery extends BaseQuery
{
    public const NAME = 'questions';
    public const PERMISSION = QuestionListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            $this->paginationArgs(),
            [
                'id' => [
                    'type' => Type::id(),
                ],
                'query' => [
                    'type' => Type::string(),
                    'description' => 'Search by name or email',
                ],
                'admin_id' => [
                    'type' => Type::id(),
                ],
                'status' => [
                    'type' => QuestionStatusEnumType::type(),
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
    ): LengthAwarePaginator {
        return $this->paginate(
            Question::query()
                ->filter($args)
                ->with($fields->getRelations())
                ->latest(),
            $args
        );
    }
}
