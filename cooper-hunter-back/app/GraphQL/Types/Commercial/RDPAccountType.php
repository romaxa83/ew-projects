<?php

namespace App\GraphQL\Types\Commercial;

use App\Enums\Formats\DatetimeEnum;
use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\NonNullType;
use App\GraphQL\Types\Users\UserMorphType;
use App\Models\Commercial\RDPAccount;

class RDPAccountType extends BaseType
{
    public const NAME = 'rdpAccountType';
    public const MODEL = RDPAccount::class;

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id(),
            ],
            'member' => [
                'type' => UserMorphType::nonNullType(),
                'is_relation' => true,
                'always' => 'id'
            ],
            'login' => [
                'type' => NonNullType::string(),
            ],
            'password' => [
                'type' => NonNullType::string(),
                'always' => ['member_id', 'member_type'],
                'resolve' => static fn(RDPAccount $a): string => $a->password,
            ],
            'active' => [
                'type' => NonNullType::boolean(),
                'description' => 'Determine if account is active on RDP system'
            ],
            'start_date' => [
                'type' => NonNullType::string(),
                'resolve' => static fn(RDPAccount $a): string => $a->start_date->format(DatetimeEnum::DATE),
                'description' => 'Date in Y-m-d format',
            ],
            'end_date' => [
                'type' => NonNullType::string(),
                'resolve' => static fn(RDPAccount $a): string => $a->end_date->format(DatetimeEnum::DATE),
                'description' => 'Date in Y-m-d format',
            ],
        ];
    }
}
