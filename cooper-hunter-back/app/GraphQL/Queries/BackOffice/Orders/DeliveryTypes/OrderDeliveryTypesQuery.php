<?php


namespace App\GraphQL\Queries\BackOffice\Orders\DeliveryTypes;


use App\GraphQL\Queries\Common\Orders\DeliveryTypes\BaseOrderDeliveryTypesQuery;
use GraphQL\Type\Definition\Type;

class OrderDeliveryTypesQuery extends BaseOrderDeliveryTypesQuery
{

    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }

    /**
     * @return array
     */
    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'published' => [
                    'type' => Type::boolean(),
                    'description' => 'Get only active/not active items',
                    'rules' => [
                        'nullable',
                        'boolean'
                    ]
                ]
            ]
        );
    }
}
