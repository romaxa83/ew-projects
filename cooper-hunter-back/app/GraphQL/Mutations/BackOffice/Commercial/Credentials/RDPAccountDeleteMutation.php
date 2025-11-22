<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Credentials;

use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\RDPAccount;
use App\Permissions\Commercial\Credentials\CredentialsUpdatePermission;
use App\Services\Commercial\RDPService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class RDPAccountDeleteMutation extends BaseMutation
{
    public const NAME = 'rdpAccountDelete';
    public const PERMISSION = CredentialsUpdatePermission::KEY;

    public function __construct(private RDPService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    Rule::exists(RDPAccount::class, 'id')
                ],
            ],
        ];
    }

    public function type(): Type
    {
        return NonNullType::boolean();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): RDPAccount {
        return makeTransaction(
            fn() => $this->service->delete(
                RDPAccount::find($args['id'])
            )
        );
    }
}