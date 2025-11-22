<?php

namespace App\GraphQL\Queries\FrontOffice\Commercial;

use App\GraphQL\Types\NonNullType;
use App\Permissions\Commercial\CommercialProjects\CommercialProjectListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class CheckRDPCredentialsQuery extends BaseQuery
{
    public const NAME = 'checkRDPCredentials';
    public const DESCRIPTION = 'Check if valid credentials are exist and no need to make repeated request';
    public const PERMISSION = CommercialProjectListPermission::KEY;

    public function __construct()
    {
        $this->setTechnicianGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        if (!$user = $this->user()) {
            return false;
        }

        return $user->hasValidRdpAccount();
    }
}