<?php

namespace App\GraphQL\Queries\FrontOffice\Catalog\Search;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Projects\ProjectSystemUnitType;
use App\Repositories\Catalog\Product\ProductRepository;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class UnitsSearchQuery extends BaseQuery
{
    public const NAME = 'unitsSearch';

    public function __construct(protected ProductRepository $repo)
    {}

    public function type(): Type
    {
        return ProjectSystemUnitType::list();
    }

    public function args(): array
    {
        return [
            'serial_numbers' => [
                'type' => Type::listOf(NonNullType::string()),
            ],
        ];
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection
    {
        return $this->repo->unitsSearch(
            $args['serial_numbers'],
            $fields->getSelect(),
            $fields->getRelations()
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'serial_numbers' => ['required', 'array'],
            'serial_numbers.*' => ['required', 'string'],
        ];
    }
}
