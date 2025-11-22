<?php

namespace App\GraphQL\Queries\BackOffice\Commercial\Commissioning;

use App\GraphQL\Types\Commercial\Commissioning\OptionAnswerType;
use App\Models\Commercial\Commissioning\Question;
use App\Permissions\Commercial\Commissionings\Question\ListPermission;
use App\Repositories\Commercial\Commissioning\OptionAnswerRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class OptionAnswerQuery extends BaseQuery
{
    public const NAME = 'commissioningOptionAnswers';
    public const PERMISSION = ListPermission::KEY;

    public function __construct(protected OptionAnswerRepository $repo)
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
                'question_id' => [
                    'type' => Type::id(),
                    ['required', 'int', Rule::exists(Question::class, 'id')]
                ],
            ]
        );
    }

    public function type(): Type
    {
        return OptionAnswerType::paginate();
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


