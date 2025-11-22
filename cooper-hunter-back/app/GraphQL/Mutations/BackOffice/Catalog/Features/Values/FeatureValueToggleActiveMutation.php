<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Features\Values;

use App\GraphQL\Types\Catalog\Features\Values;
use App\GraphQL\Types\NonNullType;
use App\Permissions\Catalog\Features\Values\UpdatePermission;
use App\Repositories\Catalog\ValueRepository;
use App\Services\Catalog\ValueService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Model;
use Rebing\GraphQL\Support\SelectFields;

class FeatureValueToggleActiveMutation extends BaseMutation
{
    public const NAME = 'featureValueToggleActive';
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
            'id' => NonNullType::id(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Model
    {
        /** @var $model Model */
        $model = $this->repo->getByFields(['id' => $args['id']]);

        return $this->service->toggleActive($model);
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', 'exists:catalog_feature_values,id'],
        ];
    }
}

