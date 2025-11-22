<?php

namespace App\GraphQL\Mutations\FrontOffice\SupportRequests;

use App\Dto\SupportRequests\SupportRequestDto;
use App\GraphQL\InputTypes\SupportRequests\SupportRequestInput;
use App\GraphQL\Types\SupportRequests\SupportRequestType;
use App\Models\Support\SupportRequest;
use App\Permissions\SupportRequests\SupportRequestCreatePermission;
use App\Services\SupportRequests\SupportRequestService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

class SupportRequestCreateMutation extends BaseMutation
{
    public const NAME = 'supportRequestCreate';
    public const PERMISSION = SupportRequestCreatePermission::KEY;

    public function __construct(protected SupportRequestService $service)
    {
        $this->setTechnicianGuard();
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
            'support_request' => SupportRequestInput::nonNullType()
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
            fn() => $this->service->create(
                SupportRequestDto::byArgs($args['support_request']),
                $this->user()
            )
        );
    }
}
