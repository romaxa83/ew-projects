<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Credentials;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\Commercial\RDPAccountType;
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

class RDPAccountUpdateMutation extends BaseMutation
{
    public const NAME = 'rdpAccountUpdate';
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
            'end_date' => [
                'type' => NonNullType::string(),
                'rules' => ['required', DatetimeEnum::DATE_RULE],
            ],
        ];
    }

    public function type(): Type
    {
        return RDPAccountType::nonNullType();
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
            fn() => $this->service->updatePasswordExpirationDate(
                RDPAccount::find($args['id']),
                $args['end_date']
            )
        );
    }
}