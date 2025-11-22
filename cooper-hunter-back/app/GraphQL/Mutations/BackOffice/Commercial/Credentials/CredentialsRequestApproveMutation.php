<?php

namespace App\GraphQL\Mutations\BackOffice\Commercial\Credentials;

use App\Enums\Commercial\CommercialCredentialsStatusEnum;
use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\NonNullType;
use App\Models\Commercial\CredentialsRequest;
use App\Permissions\Commercial\Credentials\CredentialsUpdatePermission;
use App\Services\Commercial\CommercialCredentialsService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class CredentialsRequestApproveMutation extends BaseMutation
{
    public const NAME = 'credentialsRequestApprove';
    public const PERMISSION = CredentialsUpdatePermission::KEY;

    public function __construct(private CommercialCredentialsService $service)
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return [
            'credentials_request_id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    Rule::exists(CredentialsRequest::class, 'id')
                        ->where('status', CommercialCredentialsStatusEnum::NEW)
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
        return NonNullType::boolean();
    }

    /**
     * @throws Throwable
     */
    public function doResolve(mixed $root, array $args, mixed $context, ResolveInfo $info, SelectFields $fields): bool
    {
        return makeTransaction(
            fn(): bool => $this->service->approve(
                CredentialsRequest::find($args['credentials_request_id']),
                $args['end_date']
            )
        );
    }
}
