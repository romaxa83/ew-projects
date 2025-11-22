<?php

namespace App\GraphQL\Queries\Common\Dictionaries;

use App\GraphQL\Types\Dictionaries\ProblemType;
use App\Permissions\Dictionaries\DictionaryShowPermission;
use App\Services\Dictionaries\ProblemService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseProblemsQuery extends BaseQuery
{
    public const NAME = 'problems';
    public const PERMISSION = DictionaryShowPermission::KEY;

    public function __construct(private ProblemService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        $args = $this->buildArgs(['id']);

        $args['sort']['defaultValue'] = [
            'id-desc'
        ];

        return $args;
    }

    public function type(): Type
    {
        return ProblemType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->service->show(
            $args, $fields->getRelations(),
            $fields->getSelect(),
            $this->user()
        );
    }
}
