<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Features\Specifications;

use App\Dto\Catalog\Features\Specifications\SpecificationDto;
use App\GraphQL\InputTypes\Catalog\Features\Specifications\SpecificationCreateInput;
use App\GraphQL\Types\Catalog\Features\Specifications\SpecificationType;
use App\Models\Catalog\Features\Specification;
use App\Permissions\Catalog\Features\Specifications\CreatePermission;
use App\Services\Catalog\Features\SpecificationService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SpecificationCreateMutation extends BaseMutation
{
    public const NAME = 'specificationCreate';
    public const PERMISSION = CreatePermission::KEY;

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
            'specification' => [
                'type' => SpecificationCreateInput::nonNullType(),
            ],
        ];
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Specification {
        return makeTransaction(
            fn() => $this->service->create(
                SpecificationDto::byArgs($args['specification'])
            )
        );
    }
}
