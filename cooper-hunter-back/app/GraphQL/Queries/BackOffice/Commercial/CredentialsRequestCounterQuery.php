<?php

namespace App\GraphQL\Queries\BackOffice\Commercial;

use App\GraphQL\Types\Commercial\CredentialRequestCounterType;
use App\Permissions\Commercial\Credentials\CredentialsListPermission;
use App\Services\Commercial\CommercialCredentialsService;
use Core\GraphQL\Queries\BaseQuery;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Support\Collection;
use Rebing\GraphQL\Support\SelectFields;

class CredentialsRequestCounterQuery extends BaseQuery
{
    public const NAME = 'credentialsRequestCounter';
    public const PERMISSION = CredentialsListPermission::KEY;

    public function __construct(private CommercialCredentialsService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [];
    }

    public function type(): Type
    {
        return CredentialRequestCounterType::nonNullType();
    }

    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): Collection {
        return $this->service->getCounterData($this->user());
    }
}
