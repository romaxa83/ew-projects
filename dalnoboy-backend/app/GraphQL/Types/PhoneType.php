<?php


namespace App\GraphQL\Types;


use App\Models\Phones\Phone;

class PhoneType extends BaseType
{
    public const NAME = 'PhoneType';
    public const MODEL = Phone::class;

    public function fields(): array
    {
        return [
            'phone' => [
                'type' => NonNullType::string(),
            ],
            'is_default' => [
                'type' => NonNullType::boolean(),
            ]
        ];
    }
}
