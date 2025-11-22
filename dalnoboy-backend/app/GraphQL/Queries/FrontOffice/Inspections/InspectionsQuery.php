<?php


namespace App\GraphQL\Queries\FrontOffice\Inspections;


use App\GraphQL\Queries\Common\Inspections\BaseInspectionsQuery;
use GraphQL\Type\Definition\Type;

class InspectionsQuery extends BaseInspectionsQuery
{
    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'only_mine' => [
                    'type' => Type::boolean(),
                    'defaultValue' => true
                ]
            ]
        );
    }

    protected function setQueryGuard(): void
    {
        $this->setUserGuard();
    }
}
