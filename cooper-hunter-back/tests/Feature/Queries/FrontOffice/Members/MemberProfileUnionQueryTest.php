<?php

namespace Tests\Feature\Queries\FrontOffice\Members;

use App\GraphQL\Queries\FrontOffice\Members\MemberProfileUnionQuery;
use App\GraphQL\Types\Dealers\DealerProfileType;
use App\GraphQL\Types\Technicians\TechnicianProfileType;
use App\GraphQL\Types\Users\UserProfileType;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MemberProfileUnionQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = MemberProfileUnionQuery::NAME;

    public function test_get_technician_profile(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $query = GraphQLQuery::query(self::QUERY)
            ->select(
                [
                    '... on ' . TechnicianProfileType::NAME => [
                        'id',
                        'first_name',
                        'email',
                    ],
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $technician->id,
                            'first_name' => $technician->first_name,
                            'email' => $technician->email,
                        ],
                    ]
                ]
            );
    }

    /** @test */
    public function get_technician_profile(): void
    {
        $dealer = $this->loginAsDealerWithRole();

        $query = GraphQLQuery::query(self::QUERY)
            ->select(
                [
                    '... on ' . DealerProfileType::NAME => [
                        'id',
                        'email',
                    ],
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $dealer->id,
                            'email' => $dealer->email,
                        ],
                    ]
                ]
            );
    }

    public function test_get_user_profile(): void
    {
        $user = $this->loginAsUserWithRole();

        $query = GraphQLQuery::query(self::QUERY)
            ->select(
                [
                    '... on ' . UserProfileType::NAME => [
                        'id',
                        'first_name',
                        'email',
                    ],
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $user->id,
                            'first_name' => $user->first_name,
                            'email' => $user->email,
                        ],
                    ]
                ]
            );
    }

    public function test_union_query(): void
    {
        $user = $this->loginAsUserWithRole();

        $query = GraphQLQuery::query(self::QUERY)
            ->select(
                [
                    '... on ' . UserProfileType::NAME => [
                        'id',
                        'first_name',
                        'email',
                    ],
                    '... on ' . TechnicianProfileType::NAME => [
                        'id',
                        'first_name',
                        'email',
                    ],
                    '... on ' . DealerProfileType::NAME => [
                        'id',
                        'email',
                    ],
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            'id' => $user->id,
                            'first_name' => $user->first_name,
                            'email' => $user->email,
                        ],
                    ]
                ]
            );
    }
}
