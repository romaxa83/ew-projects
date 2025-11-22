<?php

namespace App\GraphQL\Queries\BackOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireSpecificationsQuery;
use GraphQL\Type\Definition\Type;

class TireSpecificationsQuery extends BaseTireSpecificationsQuery
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
                'is_moderated' => [
                    'type' => Type::boolean(),
                ],
            ]
        );
    }
}
