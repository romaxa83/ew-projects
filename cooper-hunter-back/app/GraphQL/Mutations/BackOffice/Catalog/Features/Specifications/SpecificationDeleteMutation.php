<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations\BackOffice\Catalog\Features\Specifications;

use App\Entities\Messages\ResponseMessageEntity;
use App\GraphQL\Types\Messages\ResponseMessageType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Features\Specification;
use App\Permissions\Catalog\Features\Specifications\DeletePermission;
use App\Services\Catalog\Features\SpecificationService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SpecificationDeleteMutation extends BaseMutation
{
    public const NAME = 'specificationDelete';
    public const PERMISSION = DeletePermission::KEY;

    public function __construct(private SpecificationService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ResponseMessageType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => NonNullType::id(),
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
    ): ResponseMessageEntity {
        $s = Specification::query()
            ->whereKey($args['id'])
            ->first();

        makeTransaction(fn() => $this->service->delete($s));

        return ResponseMessageEntity::success(__('Entity deleted'));
    }

    protected function rules(array $args = []): array
    {
        return $this->returnEmptyIfGuest(
            fn() => [
                'id' => ['required', 'integer', Rule::exists(Specification::TABLE, 'id')],
            ]
        );
    }
}
