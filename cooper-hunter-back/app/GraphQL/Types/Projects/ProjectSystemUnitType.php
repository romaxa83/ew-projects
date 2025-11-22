<?php

namespace App\GraphQL\Types\Projects;

use App\GraphQL\Types\Catalog\Products\ProductType;
use App\GraphQL\Types\NonNullType;
use App\Models\Catalog\Products\Product;
use App\Models\Technicians\Technician;
use Core\Traits\Auth\AuthGuardsTrait;

class ProjectSystemUnitType extends ProductType
{
    use AuthGuardsTrait;

    public const NAME = 'ProjectSystemUnitType';

    public function fields(): array
    {
        return array_merge(
            parent::fields(),
            [
                'serial_number' => [
                    'type' => NonNullType::string(),
                    'selectable' => false,
                    'resolve' => fn(Product $product) => $product->serial_number ?: $product->unit->serial_number
                ],
                'tickets_exists' => [
                    'type' => NonNullType::boolean(),
                    'selectable' => false,
                    'resolve' => function (Product $product) {
                        $sn = $product->serial_number ?: $product->unit->serial_number;

                        $user = $this->getAuthUser();

                        if (!$user instanceof Technician) {
                            return false;
                        }
                        if (!$user->is_certified) {
                            return false;
                        }
                        return $product->tickets()
                            ->where('tickets.serial_number', $sn)
                            ->exists();
                    }
                ]
            ]
        );
    }
}
