<?php

namespace App\GraphQL\Queries\BackOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireRelationshipTypesQuery;
use GraphQL\Type\Definition\Type;

class TireRelationshipTypesQuery extends BaseTireRelationshipTypesQuery
{
    protected function setQueryGuard(): void
    {
        $this->setAdminGuard();
    }

    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'active' => [
                    'type' => Type::boolean(),
                ],
            ]
        );
    }
}
