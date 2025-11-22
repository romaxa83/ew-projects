<?php

namespace App\GraphQL\Types\Members;

use App\Contracts\Members\Member;
use App\GraphQL\Types\BaseUnionType;
use App\GraphQL\Types\Dealers\DealerProfileType;
use App\GraphQL\Types\Technicians\TechnicianProfileType;
use App\GraphQL\Types\Users\UserProfileType;
use App\Models\Dealers\Dealer;
use App\Models\Technicians\Technician;
use GraphQL\Type\Definition\NullableType;
use GraphQL\Type\Definition\Type;

class MemberProfileUnionType extends BaseUnionType
{
    public const NAME = 'MemberProfileUnionType';

    public function types(): array
    {
        return [
            TechnicianProfileType::type(),
            UserProfileType::type(),
            DealerProfileType::type()
        ];
    }

    public function resolveType(Member $value): Type|NullableType
    {
        if ($value instanceof Technician) {
            return TechnicianProfileType::type();
        }

        if ($value instanceof Dealer) {
            return DealerProfileType::type();
        }

        return UserProfileType::type();
    }
}
