<?php

namespace App\GraphQL\Mutations\BackOffice\Technicians;

use App\GraphQL\Types\NonNullType;
use App\Models\Technicians\Technician;
use App\Permissions\Technicians\TechnicianRestorePermission;
use App\Services\Technicians\TechnicianService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TechnicianRestoreMutation extends BaseMutation
{
    public const NAME = 'technicianRestore';
    public const PERMISSION = TechnicianRestorePermission::KEY;

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
            ->withTrashed()
            ->whereKey($args['ids'])
            ->get();

        return makeTransaction(
            fn() => $this->service->restore($models)
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


