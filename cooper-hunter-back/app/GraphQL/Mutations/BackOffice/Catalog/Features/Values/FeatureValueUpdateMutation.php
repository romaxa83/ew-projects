<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Features\Values;

use App\Dto\Catalog\ValueDto;
use App\GraphQL\Types\Catalog\Features\Values;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Feature;
use App\Models\Catalog\Features\Metric;
use App\Models\Catalog\Features\Value;
use App\Permissions\Catalog\Features\Values\UpdatePermission;
use App\Repositories\Catalog\ValueRepository;
use App\Services\Catalog\ValueService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class FeatureValueUpdateMutation extends BaseMutation
{
    public const NAME = 'featureValueUpdate';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected ValueService $service,
        protected ValueRepository $repo
    ) {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Values\ValueType::type();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    'required',
                    'int',
                    Rule::exists(Value::class, 'id'),
                ]
            ],
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
        /** @var $model Value */
        $model = $this->repo->getByFields(['id' => $args['id']]);
        return makeTransaction(
            fn() => $this->service->update(
                ValueDto::byArgs($args),
                $model
            )
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'title' => [
                'required',
                'string',
                Rule::unique(Value::class, 'title')->ignore($args['id'])
            ],
        ];
    }
}
