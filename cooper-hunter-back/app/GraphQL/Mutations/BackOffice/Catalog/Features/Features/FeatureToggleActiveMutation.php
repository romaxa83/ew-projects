<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Features\Features;

use App\GraphQL\Types\Catalog\Features\Features\FeatureType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Feature;
use App\Permissions\Catalog\Features\Features\UpdatePermission;
use App\Repositories\Catalog\FeatureRepository;
use App\Services\Catalog\FeatureService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Database\Eloquent\Model;
use Rebing\GraphQL\Support\SelectFields;

class FeatureToggleActiveMutation extends BaseMutation
{
    public const NAME = 'featureToggleActive';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(
        protected FeatureService $service,
        protected FeatureRepository $repo
    ) {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return FeatureType::type();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Model
    {
        return $this->service->toggleActive(
            Feature::query()->findOrFail($args['id'])
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', 'exists:catalog_features,id'],
        ];
    }
}

