<?php

namespace App\GraphQL\Mutations\BackOffice\Technicians;

use App\GraphQL\Types\NonNullType;
use App\Models\Technicians\Technician;
use App\Permissions\Technicians\TechnicianSoftDeletePermission;
use App\Services\Technicians\TechnicianService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TechnicianSoftDeleteMutation extends BaseMutation
{
    public const NAME = 'technicianSoftDelete';
    public const PERMISSION = TechnicianSoftDeletePermission::KEY;

    public function __construct(protected TechnicianService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'ids' => Type::nonNull(Type::listOf(NonNullType::id())),
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        $models = Technician::query()
            ->whereKey($args['ids'])
            ->get();

        return makeTransaction(
            fn() => $this->service->softDelete($models)
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['required', 'int', Rule::exists(Technician::TABLE, 'id')]
        ];
    }
}

