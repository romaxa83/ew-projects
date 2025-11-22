<?php

namespace App\GraphQL\Queries\FrontOffice\Commercial;

use App\GraphQL\Types\Commercial\RDPAccountType;
use App\Models\Commercial\RDPAccount;
use App\Permissions\Commercial\Credentials\CredentialsListPermission;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;

class RDPAccountQuery extends BaseQuery
{
    public const NAME = 'rdpAccount';
    public const PERMISSION = CredentialsListPermission::KEY;

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
        return RDPAccountType::type();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): ?RDPAccount {
        $account = $this->user()->rdpAccount;

        if ($account?->active) {
            return $account;
        }

        return null;
    }
}