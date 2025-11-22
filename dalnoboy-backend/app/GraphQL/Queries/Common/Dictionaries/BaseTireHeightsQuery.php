<?php

namespace App\GraphQL\Queries\Common\Dictionaries;

use App\GraphQL\Types\Dictionaries\TireHeightType;
use App\Permissions\Dictionaries\DictionaryShowPermission;
use App\Services\Dictionaries\TireHeightService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseTireHeightsQuery extends BaseQuery
{
    public const NAME = 'tireHeights';
    public const PERMISSION = DictionaryShowPermission::KEY;

    public function __construct(private TireHeightService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        $args = $this->buildArgs(['value']);

        $args['sort']['defaultValue'] = [
            'value-asc'
        ];

        return $args;
    }

    public function type(): Type
    {
        return TireHeightType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return $this->service->show(
            $args,
            $fields->getRelations(),
            $fields->getSelect(),
            $this->user()
        );
    }
}
