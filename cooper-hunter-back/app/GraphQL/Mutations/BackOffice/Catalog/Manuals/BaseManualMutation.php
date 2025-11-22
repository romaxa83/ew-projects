<?php

namespace App\GraphQL\Mutations\BackOffice\Catalog\Manuals;

use App\GraphQL\Types\Catalog\Manuals\ManualType;
use App\Services\Catalog\Manuals\ManualService;
use Core\GraphQL\Mutations\BaseMutation;
use GraphQL\Type\Definition\Type;

abstract class BaseManualMutation extends BaseMutation
{
    public function __construct(protected ManualService $service)
    {
        $this->setAdminGuard();
    }

    public function type(): Type
    {
        return ManualType::list();
    }
}
