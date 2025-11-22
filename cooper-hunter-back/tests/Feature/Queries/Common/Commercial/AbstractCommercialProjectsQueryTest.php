<?php

namespace Tests\Feature\Queries\Common\Commercial;

use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AbstractCommercialProjectsQueryTest extends TestCase
{
    use DatabaseTransactions;

    protected function getQuery(string $query, array $args = [], bool $forPaginate = true): array
    {
        $select = [
            'id',
            'member' => [
                'id',
                'type',
                'name',
                'email',
            ],
            'status',
            'name',
            'address_line_1',
            'address_line_2',
            'city',
            'state' => [
                'id'
            ],
            'country' => [
                'id'
            ],
            'zip',
            'first_name',
            'last_name',
            'phone',
            'email',
            'company_name',
            'company_address',
            'description',
            'estimate_start_date',
            'estimate_end_date',
            'project_protocols' => [
                'id'
            ]
        ];

        $select = $forPaginate ? ['data' => $select] : $select;

        return GraphQLQuery::query($query)
            ->args($args)
            ->select($select)
            ->make();
    }
}
