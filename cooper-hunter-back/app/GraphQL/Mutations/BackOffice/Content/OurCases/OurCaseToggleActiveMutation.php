<?php

namespace App\GraphQL\Mutations\BackOffice\Content\OurCases;

use App\GraphQL\Types\Content\OurCase\OurCaseType;
use App\Models\BaseModel;
use App\Models\Content\OurCases\OurCase;
use App\Permissions\Content\OurCases\OurCaseUpdatePermission;
use Core\GraphQL\Mutations\BaseToggleActiveMutation;
use GraphQL\Type\Definition\Type;

class OurCaseToggleActiveMutation extends BaseToggleActiveMutation
{
    public const NAME = 'ourCaseToggleActive';
    public const PERMISSION = OurCaseUpdatePermission::KEY;

    public function type(): Type
    {
        return OurCaseType::nonNullType();
    }

    public function model(): BaseModel|string
    {
        return OurCase::class;
    }
}
