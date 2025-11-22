<?php

namespace App\GraphQL\Types\Companies;

use App\GraphQL\Types\BaseType;
use App\Models\Companies\Company;

class PublicCompanyInfoType extends BaseType
{
    public const NAME = 'PublicCompanyInfoType';
    public const MODEL = Company::class;

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
            ]
        );
    }
}
