<?php

namespace App\GraphQL\Queries\BackOffice\Warranty\WarrantyRegistrations;

use App\GraphQL\Types\Enums\Projects\Systems\WarrantyStatusEnumType;
use App\GraphQL\Types\Warranty\WarrantyRegistrations\WarrantyRegistrationType;
use App\Models\Warranty\WarrantyRegistration;
use App\Permissions\Warranty\WarrantyRegistration\WarrantyRegistrationListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Rebing\GraphQL\Support\SelectFields;

class WarrantyRegistrationQuery extends BaseQuery
{
    public const NAME = 'warrantyRegistrations';
    public const PERMISSION = WarrantyRegistrationListPermission::KEY;

    public function __construct()
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            [
                'member_name' => [
                    'type' => Type::string(),
                    'description' => 'Filter by member name',
                ],
                'warranty_status' => [
                    'type' => WarrantyStatusEnumType::type(),
                    'description' => 'Filter by status',
                ],
            ],
            parent::args(),
        );
    }

    public function type(): Type
    {
        return WarrantyRegistrationType::paginate();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): LengthAwarePaginator {
        return WarrantyRegistration::query()
            ->filter($args)
            ->with($fields->getRelations())
            ->orderByDesc('created_at')
            ->paginate(perPage: $args['per_page'], page: $args['page']);
    }
}
