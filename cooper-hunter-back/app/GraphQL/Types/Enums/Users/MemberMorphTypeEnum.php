<?php

namespace App\GraphQL\Types\Enums\Users;

use App\Enums\Users\UserMorphEnum;
use App\GraphQL\Types\BaseEnumType;

class MemberMorphTypeEnum extends BaseEnumType
{
    public const NAME = 'MemberMorphTypeEnum';
    public const DESCRIPTION = 'List available member types';
    public const ENUM_CLASS = UserMorphEnum::class;

    public function attributes(): array
    {
        return array_merge(
            parent::attributes(),
            [
                'values' => collect(UserMorphEnum::getMemberValues())
                    ->mapWithKeys(static fn(string $type) => [$type => $type])
                    ->toArray()
            ]
        );
    }
}
