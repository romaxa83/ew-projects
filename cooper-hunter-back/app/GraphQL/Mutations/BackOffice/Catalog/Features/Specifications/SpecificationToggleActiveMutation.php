<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Features\Specifications;

use App\GraphQL\Types\Catalog\Features\Specifications\SpecificationType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Specification;
use App\Permissions\Catalog\Features\Specifications\UpdatePermission;
use App\Services\Catalog\Features\SpecificationService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class SpecificationToggleActiveMutation extends BaseMutation
{
    public const NAME = 'specificationToggleActive';
    public const PERMISSION = UpdatePermission::KEY;

    public function __construct(protected SpecificationService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return SpecificationType::type();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
        ];
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Specification
    {
        $s = Specification::query()->findOrFail($args['id']);

        return $this->service->toggle($s);
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Specification::TABLE, 'id')],
        ];
    }

}
