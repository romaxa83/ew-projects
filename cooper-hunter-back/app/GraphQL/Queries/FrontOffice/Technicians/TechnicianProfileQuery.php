<?php

namespace App\GraphQL\Queries\FrontOffice\Technicians;

use App\GraphQL\Types\Technicians\TechnicianProfileType;
use App\Models\Technicians\Technician;
use Closure;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class TechnicianProfileQuery extends BaseQuery
{
    public const NAME = 'technicianProfile';

    public function __construct()
    {
        $this->setTechnicianGuard();
    }

    public function authorize($root, array $args, $ctx, ResolveInfo $info = null, Closure $fields = null): bool
    {
        return $this->authCheck();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return TechnicianProfileType::type();
    }

    public function doResolve($root, array $args, $context, ResolveInfo $info, SelectFields $fields): Technician
    {
        return $this->user()
            ->load($fields->getRelations());
    }
}
