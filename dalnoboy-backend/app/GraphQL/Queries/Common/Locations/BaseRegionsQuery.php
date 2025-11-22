<?php


namespace App\GraphQL\Queries\Common\Locations;


use App\GraphQL\Types\Locations\RegionType;
use App\Models\Locations\Region;
use App\Permissions\Locations\RegionShowPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseRegionsQuery extends BaseQuery
{
    public const NAME = 'regions';
    public const PERMISSION = RegionShowPermission::KEY;

    public function __construct()
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return RegionType::nonNullList();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return Region::filter(
            [
                'sort' => [
                    'title-asc'
                ]
            ]
        )
            ->with($fields->getRelations())
            ->get();
    }
}
