<?php


namespace App\GraphQL\InputTypes\Alert;


use App\GraphQL\Types\BaseInputType;
use App\GraphQL\Types\Enums\Users\MemberMorphTypeEnum;
use App\GraphQL\Types\NonNullType;

class AlertRecipientInputType extends BaseInputType
{
    public const NAME = 'AlertRecipientInputType';

    public function fields(): array
    {
        return [
            'id' => [
                'type' => NonNullType::id()
            ],
            'type' => [
                'type' => MemberMorphTypeEnum::nonNullType()
            ]
        ];
    }
}
