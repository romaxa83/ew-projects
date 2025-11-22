<?php

namespace App\GraphQL\Mutations\Common\SupportRequests;

use App\Dto\SupportRequests\SupportRequestMessageDto;
use App\GraphQL\InputTypes\SupportRequests\SupportRequestMessageInput;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\SupportRequests\SupportRequestMessageType;
use App\Models\Support\SupportRequest;
use App\Models\Support\SupportRequestMessage;
use App\Permissions\SupportRequests\SupportRequestAnswerPermission;
use App\Services\SupportRequests\SupportRequestService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseSupportRequestAnswerMutation extends BaseMutation
{
    public const NAME = 'supportRequestAnswer';
    public const PERMISSION = SupportRequestAnswerPermission::KEY;

    public function __construct(protected SupportRequestService $service)
    {
        $this->setMutationGuard();
    }

    abstract protected function setMutationGuard(): void;

    /**
     * @return Type
     */
    public function type(): Type
    {
        return SupportRequestMessageType::nonNullType();
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
            ],
            'message' => [
                'type' => SupportRequestMessageInput::nonNullType()
            ]
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return SupportRequestMessage
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): SupportRequestMessage {
        return makeTransaction(
            fn() => $this->service->answer(
                $args['id'],
                SupportRequestMessageDto::byArgs($args['message']),
                $this->user(),
            )
        );
    }

}
