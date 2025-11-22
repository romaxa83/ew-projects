<?php

namespace App\GraphQL\Queries\FrontOffice\Orders;

use App\GraphQL\Queries\Common\Orders\BaseOrderQuery;
use App\GraphQL\Types\Enums\Orders\OrderFilterTabTypeEnum;
use App\GraphQL\Types\Enums\Orders\OrderFilterTrkNumberExistsTypeEnum;
use App\Services\Orders\OrderService;
use GraphQL\Type\Definition\Type;

class OrderQuery extends BaseOrderQuery
{
    public function __construct(protected OrderService $orderService)
    {
        $this->setTechnicianGuard();
    }

    /**
     * @return array[]
     */
    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'trk_number_exists' => [
                    'type' => OrderFilterTrkNumberExistsTypeEnum::type(),
                    'description' => 'Filter by trk number'
                ],
                'tab' => [
                    'type' => OrderFilterTabTypeEnum::type(),
                ],
                'limit' => [
                    'type' => Type::int(),
                    'defaultValue' => 5
                ],
            ]
        );
    }
}
