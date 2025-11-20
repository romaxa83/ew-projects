<?php

namespace App\GraphQL\Types\Sips;

use App\GraphQL\Types\BaseType;
use App\GraphQL\Types\Employees\EmployeeType;
use App\GraphQL\Types\NonNullType;
use App\Models\Sips\Sip;
use GraphQL\Type\Definition\Type;

class SipType extends BaseType
{
    public const NAME = 'SipType';
    public const MODEL = Sip::class;

    public function fields(): array
    {
        $timezone = getallheaders()['Timezone'] ?? config('app.timezone');
//        dd($timezone);
        return array_merge(
            parent::fields(),
            [
                'number' => [
                    'type' => Type::string(),
                ],
                'employee' => [
                    'type' => EmployeeType::Type(),
                ],
                'created_at' => [
                    'type' => NonNullType::string(),
                    'resolve' => static fn(Sip $m) => $m->created_at
                        ->setTimezone($timezone)
                    ,
                ],
            ]
        );
    }
}
