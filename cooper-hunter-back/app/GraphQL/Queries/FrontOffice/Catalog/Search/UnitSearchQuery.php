<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Search;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Projects\ProjectSystemUnitType;
use App\Models\Catalog\Products\Product;
use App\Repositories\Catalog\Product\ProductRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class UnitSearchQuery extends BaseQuery
{
    public const NAME = 'unitSearch';

    public function __construct(protected ProductRepository $repo)
    {}

    public function type(): Type
    {
        return ProjectSystemUnitType::type();
    }

    public function args(): array
    {
        return [
            'serial_number' => NonNullType::string(),
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?Product
    {
        return $this->repo->unitSearch(
            $args['serial_number'],
            $fields->getSelect(),
            $fields->getRelations()
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'serial_number' => ['required', 'string', 'min:3'],
        ];
    }
}
