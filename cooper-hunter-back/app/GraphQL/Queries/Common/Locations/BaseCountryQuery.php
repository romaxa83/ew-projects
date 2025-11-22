<?php

namespace App\GraphQL\Queries\Common\Locations;

use App\GraphQL\Types\Locations\CountryType;
use App\Repositories\Locations\CountryRepository;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseCountryQuery extends BaseQuery
{
    public const NAME = 'countries';

    public function __construct(protected CountryRepository $repo)
    {}

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool {
        return true;
    }

    public function type(): Type
    {
        return Type::listOf(CountryType::type());
    }

    public function args(): array
    {
        return [
            'id' => ['type' => Type::id()],
            'name' => ['type' => Type::string()],
            'default' => ['type' => Type::boolean()],
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
        return $this->repo->listForFront($args, ['translation', 'translations']);
    }
}

