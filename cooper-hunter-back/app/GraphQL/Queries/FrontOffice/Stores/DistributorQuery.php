<?php

declare(strict_types=1);

namespace App\GraphQL\Queries\FrontOffice\Stores;

use App\GraphQL\InputTypes\Stores\Distributors\CoordinateInput;
use App\GraphQL\Queries\Common\Stores\BaseDistributorQuery;

class DistributorQuery extends BaseDistributorQuery
{
    public function args(): array
    {
        return array_merge(
            parent::args(),
            [
                'coordinates' => [
                    'type' => CoordinateInput::nonNullType(),
                    'description' => 'Some user position. Can be center of displayed map, or anything else that can be helpful to filter distributors.',
                ],
            ]
        );
    }

    protected function setQueryGuard(): void
    {
    }

    protected function initArgs(array $args): array
    {
        $args['active'] = true;

        return $args;
    }
}
