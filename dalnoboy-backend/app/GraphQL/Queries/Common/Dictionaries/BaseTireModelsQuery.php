<?php

namespace App\GraphQL\Queries\Common\Dictionaries;

use App\GraphQL\Types\Dictionaries\TireModelType;
use App\Permissions\Dictionaries\DictionaryShowPermission;
use App\Services\Dictionaries\TireModelService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseTireModelsQuery extends BaseQuery
{
    public const NAME = 'tireModels';
    public const PERMISSION = DictionaryShowPermission::KEY;

    public function __construct(private TireModelService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        $args = array_merge(
            $this->buildArgs(['id'], ['title']),
            [
                'tire_make' => [
                    'type' => Type::int()
                ],
            ]
        );

        $args['sort']['defaultValue'] = [
            'id-desc'
        ];

        return $args;
    }

    public function type(): Type
    {
        return TireModelType::paginate();
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
