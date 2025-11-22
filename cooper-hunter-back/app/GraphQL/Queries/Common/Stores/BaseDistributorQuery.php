<?php

namespace App\GraphQL\Queries\Common\Stores;

use App\GraphQL\InputTypes\Stores\Distributors\CoordinateInRadiusFilterInput;
use App\GraphQL\Types\Stores\DistributorType;
use App\Services\Stores\DistributorService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseDistributorQuery extends BaseQuery
{
    public const NAME = 'distributor';

    public function __construct(protected DistributorService $service)
    {
        $this->setQueryGuard();
    }

    abstract protected function setQueryGuard(): void;

    public function args(): array
    {
        return array_merge(
            [
                'radius' => [
                    'type' => CoordinateInRadiusFilterInput::type(),
                    'description' => 'Starting point for filtering distributors within a given radius.',
                ],
                'query' => [
                    'type' => Type::string(),
                    'description' => 'Filters distributors by address or zip code.'
                ],
            ],
            $this->getIdArgs(),
        );
    }

    public function type(): Type
    {
        return DistributorType::nonNullList();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->getList($args, $fields->getRelations());
    }
}
