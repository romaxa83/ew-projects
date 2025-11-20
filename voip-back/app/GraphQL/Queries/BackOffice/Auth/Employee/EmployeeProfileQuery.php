<?php

namespace App\GraphQL\Queries\BackOffice\Auth\Employee;

use App\GraphQL\Types\Employees\EmployeeProfileType;
use App\Models\BaseAuthenticatable;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\{ResolveInfo, Type};
use Rebing\GraphQL\Support\SelectFields;

class EmployeeProfileQuery extends BaseQuery
{
    public const NAME = 'EmployeeProfile';

    public function __construct()
    {
        $this->setEmployeeGuard();
    }

    public function authorize(
        mixed $root,
        array $args,
        mixed $ctx,
        ResolveInfo $info = null,
        Closure $fields = null
    ): bool
    {
        return $this->authCheck();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return EmployeeProfileType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): BaseAuthenticatable
    {
        return $this->user();
    }
}
