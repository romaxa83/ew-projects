<?php

namespace App\GraphQL\Queries\Common\Tires;

use App\GraphQL\Types\Tires\TireType;
use App\Permissions\Tires\TireShowPermission;
use App\Services\Tires\TireService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseTiresQuery extends BaseQuery
{
    public const NAME = 'tires';
    public const PERMISSION = TireShowPermission::KEY;

    public function __construct(private TireService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return array_merge(
            $this->buildArgs(
                [
                    'fields' => [
                        'id'
                    ],
                    'default_value' => [
                        'id-desc'
                    ]
                ],
                ['serial_number']
            ),
            [
                'tire_make' => [
                    'type' => Type::int()
                ],
                'tire_model' => [
                    'type' => Type::int()
                ],
                'tire_type' => [
                    'type' => Type::int()
                ],
                'tire_relationship_type' => [
                    'type' => Type::int()
                ],
                'tire_size' => [
                    'type' => Type::int()
                ],
            ]
        );
    }

    public function type(): Type
    {
        return TireType::paginate();
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
