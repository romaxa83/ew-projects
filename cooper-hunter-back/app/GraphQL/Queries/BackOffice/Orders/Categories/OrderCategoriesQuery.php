<?php


namespace App\GraphQL\Queries\BackOffice\Orders\Categories;


use App\GraphQL\Queries\Common\Orders\Categories\BaseOrderCategoriesQuery;
use GraphQL\Type\Definition\Type;

class OrderCategoriesQuery extends BaseOrderCategoriesQuery
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
            ],
            [
                'for_edit' => [
                    'type' => Type::boolean(),
                    'defaultValue' => true,
                    'description' => 'Getting order categories list for edit (for CRUD)',
                ]
            ]
        );
    }
}
