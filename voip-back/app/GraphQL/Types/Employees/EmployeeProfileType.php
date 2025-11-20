<?php

namespace App\GraphQL\Types\Employees;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Departments\DepartmentType;
use App\GraphQL\Types\Localization\LanguageType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Roles\PermissionType;
use App\GraphQL\Types\Roles\RoleType;
use App\GraphQL\Types\Sips\SipType;
use App\Models\Employees\Employee;
use App\Models\Reports\Report;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use App\GraphQL\Types\Enums;

class EmployeeProfileType extends BaseType
{
    public const NAME = 'EmployeeProfileType';
    public const MODEL = Employee::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'status' => [
                    'type' => Enums\Employees\StatusEnum::Type(),
                ],
                'email' => [
                    'type' => NonNullType::string(),
                ],
                'first_name' => [
                    'type' => Type::string(),
                ],
                'last_name' => [
                    'type' => Type::string(),
                ],
                'language' => [
                    'type' => LanguageType::type(),
                    'is_relation' => true,
                ],
                'roles' => [
                    'type' => Type::listOf(RoleType::type()),
                ],
                'permissions' => [
                    'type' => Type::listOf(PermissionType::type()),
                ],
                'department' => [
                    'type' => DepartmentType::nonNullType(),
                ],
                'sip' => [
                    'type' => SipType::Type(),
                ],
                'report_id' => [
                    'type' => Type::int(),
                    'resolve' => static fn(Employee $m): int => $m->report->id,
                ],
            ]
        );
    }

    protected function resolvePermissionsField(Employee $root, $args): Collection
    {
        return $root->getAllPermissions();
    }
}
