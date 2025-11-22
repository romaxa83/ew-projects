<?php

namespace App\GraphQL\Mutations\BackOffice\Stores\Distributors;

use App\GraphQL\Types\Stores\DistributorType;
use App\Models\BaseModel;
use App\Models\Stores\Distributor;
use App\Permissions\Stores\Distributors\DistributorUpdatePermission;
use Core\GraphQL\Mutations\BaseToggleActiveMutation;
use GraphQL\Type\Definition\Type;

class DistributorToggleActiveMutation extends BaseToggleActiveMutation
{
    public const NAME = 'distributorToggleActive';
    public const PERMISSION = DistributorUpdatePermission::KEY;

    public function type(): Type
    {
        return DistributorType::nonNullType();
    }

    protected function model(): BaseModel|string
    {
        return Distributor::class;
    }
}
