<?php

namespace App\GraphQL\Mutations\BackOffice\SupportRequests;

use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\SupportRequests\SupportRequestType;
use App\Models\Support\SupportRequest;
use App\Permissions\SupportRequests\SupportRequestClosePermission;
use App\Services\SupportRequests\SupportRequestService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SupportRequestCloseMutation extends BaseMutation
{
    public const NAME = 'supportRequestClose';
    public const PERMISSION = SupportRequestClosePermission::KEY;

    public function __construct(protected SupportRequestService $service)
    {
        $this->setAdminGuard();
    }

    /**
     * @return Type
     */
    public function type(): Type
    {
        return SupportRequestType::nonNullType();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'rules' => [
                    Rule::exists(SupportRequest::class, 'id')
                        ->where('is_closed', 0)
                ]
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return SupportRequest
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): SupportRequest {
        return makeTransaction(
            fn() => $this->service->close(
                $args['id'],
                $this->user(),
            )
        );
    }
}
