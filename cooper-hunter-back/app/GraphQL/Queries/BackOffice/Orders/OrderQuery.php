<?php


namespace App\GraphQL\Queries\BackOffice\Orders;

use App\GraphQL\Queries\Common\Orders\BaseOrderQuery;
use App\Services\Orders\OrderService;
use GraphQL\Type\Definition\Type;

class OrderQuery extends BaseOrderQuery
{
    public function __construct(protected OrderService $orderService)
    {
        $this->setAdminGuard();
    }

    /**
     * @return array[]
     */
    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'technician_id' => [
                    'type' => Type::id(),
                    'description' => 'Filter by technician ID'
                ],
                'technician_name' => [
                    'type' => Type::string(),
                    'description' => 'Filter by technician name'
                ],
                'recipient_name' => [
                    'type' => Type::string(),
                    'description' => 'Filter by recipient name (shipping)'
                ],
                'date_from' => [
                    'type' => Type::string(),
                    'description' => 'Filter by start create date. Format: Y-m-d',
                    'rules' => [
                        'nullable',
                        'date_format:Y-m-d'
                    ],
                ],
                'date_to' => [
                    'type' => Type::string(),
                    'description' => 'Filter by end create date. Format: Y-m-d',
                    'rules' => [
                        'nullable',
                        'date_format:Y-m-d'
                    ],
                ],
                'serial_number' => [
                    'type' => Type::string(),
                    'description' => 'Filter by product serial number',
                ],
                'limit' => [
                    'type' => Type::int(),
                    'defaultValue' => 10
                ],
            ]
        );
    }
}
