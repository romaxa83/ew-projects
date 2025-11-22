<?php

namespace App\GraphQL\Mutations\FrontOffice\Projects;

use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;
use App\Models\Projects\System;
use App\Permissions\Projects\ProjectDeletePermission;
use App\Services\Projects\SystemService;
use Core\Exceptions\TranslatedException;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class MemberProjectSystemUnitDeleteMutation extends BaseMutation
{
    public const NAME = 'memberProjectSystemUnitDeleteMutation';
    public const PERMISSION = ProjectDeletePermission::KEY;

    public function __construct(protected SystemService $service)
    {
        $this->setMemberGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'system_id' => [
                'type' => NonNullType::id(),
            ],
            'unit_ids' => [
                'type' => NonNullType::listOf(NonNullType::id()),
            ],
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $system = System::query()->findOrFail($args['system_id']);

        if ($system->warranty_status->requestSent()) {
            throw new TranslatedException(__('Unable to remove units under warranty from the system'));
        }

        return $this->service->deleteUnits($system, $args['unit_ids']);
    }

    protected function rules(array $args = []): array
    {
        return [
            'system_id' => [
                'required',
                'int',
                Rule::exists(System::TABLE, 'id')
            ],
            'unit_ids.*' => ['required', 'int'],
            'unit_ids' => ['required', 'array', Rule::exists(Product::TABLE, 'id')],
        ];
    }
}
