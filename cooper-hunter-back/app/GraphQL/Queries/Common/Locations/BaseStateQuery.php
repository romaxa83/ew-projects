<?php

namespace App\GraphQL\Queries\Common\Locations;

use App\GraphQL\Types\Locations\StateType;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

abstract class BaseStateQuery extends BaseQuery
{
    public const NAME = 'states';

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
        return Type::listOf(StateType::type());
    }

    public function args(): array
    {
        return [
            'id' => ['type' => Type::id()],
            'name' => ['type' => Type::string()],
            'country_id' => [
                'type' => Type::id(),
                'rules' => ['nullable', 'int', Rule::exists(Country::class, 'id')],
            ],
            'country_code' => [
                'type' => Type::string(),
                'rules' => ['nullable', 'string', Rule::exists(Country::class, 'country_code')],
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
        return State::query()
            ->where('status', true)
            ->select($fields->getSelect() ?: ['id'])
            ->with(['translation', 'translations'])
            ->filter($args)
            ->orderBy('slug')
            ->get();
    }
}
