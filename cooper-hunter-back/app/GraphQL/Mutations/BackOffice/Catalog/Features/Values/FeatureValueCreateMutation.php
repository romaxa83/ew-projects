<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Features\Values;

use App\Dto\Catalog\ValueDto;
use App\GraphQL\Types\Catalog\Features\Values\ValueType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Metric;
use App\Models\Catalog\Features\Value;
use App\Permissions\Catalog\Features\Values\CreatePermission;
use App\Services\Catalog\ValueService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class FeatureValueCreateMutation extends BaseMutation
{
    public const NAME = 'featureValueCreate';
    public const PERMISSION = CreatePermission::KEY;

    public function __construct(protected ValueService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ValueType::type();
    }

    public function args(): array
    {
        return [
            'feature_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Feature::class, 'id')
                ]
            ],
            'metric_id' => [
                'type' => Type::id(),
                'rules' => [
                    'nullable',
                    'int',
                    Rule::exists(Metric::class, 'id')
                ]
            ],
            'active' => [
                'type' => Type::boolean(),
            ],
            'title' => [
                'type' => NonNullType::string(),
                'rules' => ['required', 'string', Rule::unique(Value::class, 'title')],
            ],
        ];
    }

    /**
     * @param $root
     * @param array $args
     * @param $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return Value
     * @throws Throwable
     */
    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Value
    {
        return makeTransaction(
            fn() => $this->service->create(
                ValueDto::byArgs($args)
            )
        );
    }
}
