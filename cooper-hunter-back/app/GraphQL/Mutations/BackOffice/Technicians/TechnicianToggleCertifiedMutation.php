<?php

namespace App\GraphQL\Mutations\BackOffice\Technicians;

use App\GraphQL\Types\NonNullType;
use App\Models\Technicians\Technician;
use App\Permissions\Technicians\TechnicianToggleStatusPermission;
use App\Services\Technicians\TechnicianService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;

class TechnicianToggleCertifiedMutation extends BaseMutation
{
    public const NAME = 'technicianToggleCertified';
    public const PERMISSION = TechnicianToggleStatusPermission::KEY;

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
            'id' => [
                'type' => NonNullType::id(),
            ],
        ];
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return $this->service->toggleCertified(
            Technician::query()->findOrFail($args['id'])
        );
    }

    protected function rules(array $args = []): array
    {
        return [
            'id' => ['required', 'int', Rule::exists(Technician::TABLE, 'id')],
        ];
    }
}
