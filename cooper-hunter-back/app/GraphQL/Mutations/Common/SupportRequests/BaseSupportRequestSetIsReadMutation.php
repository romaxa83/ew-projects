<?php

namespace App\GraphQL\Mutations\Common\SupportRequests;

use App\GraphQL\Types\NonNullType;
use App\Models\Support\SupportRequest;
use App\Permissions\SupportRequests\SupportRequestAnswerPermission;
use App\Services\SupportRequests\SupportRequestService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\Rule;
use Rebing\GraphQL\Support\SelectFields;
use Throwable;

abstract class BaseSupportRequestSetIsReadMutation extends BaseMutation
{
    public const NAME = 'supportRequestSetIsRead';
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
        return NonNullType::boolean();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::id(),
                'rules' => [
                    'required_with:messages_ids',
                    Rule::exists(SupportRequest::class, 'id')
                ],
                'description' => 'If "id" is null, flag "is_read" will be set on all messages in all requests.'
            ],
            'messages_ids' => [
                'type' => Type::listOf(
                    NonNullType::id()
                ),
                'description' => 'If "messages_ids" is empty, flag "is_read" will be set on all messages in request.'
            ],
        ];
    }

    /**
     * @param mixed $root
     * @param array $args
     * @param mixed $context
     * @param ResolveInfo $info
     * @param SelectFields $fields
     * @return bool
     * @throws Throwable
     */
    public function doResolve(
        mixed $root,
        array $args,
        mixed $context,
        ResolveInfo $info,
        SelectFields $fields
    ): bool {
        return makeTransaction(
            fn() => $this->service->setIsRead(
                data_get($args, 'id'),
                data_get($args, 'messages_ids', []),
                $this->user(),
            )
        );
    }

}
