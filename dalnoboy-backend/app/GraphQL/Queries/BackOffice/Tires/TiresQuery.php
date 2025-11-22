<?php

namespace App\GraphQL\Queries\BackOffice\Tires;

use App\GraphQL\Queries\Common\Tires\BaseTiresQuery;
use GraphQL\Type\Definition\Type;

class TiresQuery extends BaseTiresQuery
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
