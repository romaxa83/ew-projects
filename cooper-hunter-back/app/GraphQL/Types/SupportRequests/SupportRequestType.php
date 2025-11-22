<?php

namespace App\GraphQL\Types\SupportRequests;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\SupportRequests\Subjects\SupportRequestSubjectType;
use App\GraphQL\Types\Technicians\TechnicianType;
use App\Models\Support\SupportRequest;

class SupportRequestType extends BaseType
{
    public const NAME = 'SupportRequestType';
    public const MODEL = SupportRequest::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
                'description' => 'Request ID',
            ],
            'subject' => [
                'type' => SupportRequestSubjectType::nonNullType(),
                'is_relation' => true,
            ],
            'technician' => [
                'type' => TechnicianType::nonNullType(),
                'is_relation' => true,
            ],
            'messages' => [
                'type' => SupportRequestMessageType::list(),
                'is_relation' => true
            ],
            'is_read' => [
                'type' => NonNullType::boolean(),
                'resolve' => fn(SupportRequest $supportRequest) => !empty($supportRequest->is_read)
            ],
            'is_closed' => [
                'type' => NonNullType::boolean(),
            ],
            'created_at' => [
                'type' => NonNullType::string()
            ],
        ];
    }


}
