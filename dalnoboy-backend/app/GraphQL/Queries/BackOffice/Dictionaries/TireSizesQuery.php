<?php

namespace App\GraphQL\Queries\BackOffice\Dictionaries;

use App\GraphQL\Queries\Common\Dictionaries\BaseTireSizesQuery;
use GraphQL\Type\Definition\Type;

class TireSizesQuery extends BaseTireSizesQuery
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
