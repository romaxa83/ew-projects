<?php

namespace Tests\Feature\Queries\BackOffice\AboutCompany;

use App\GraphQL\Queries\BackOffice\About\AboutCompanyQuery;
use App\Models\About\AboutCompany;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AboutCompanyQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = AboutCompanyQuery::NAME;

    public function test_get_about_company(): void
    {
        $this->loginAsSuperAdmin();

        $about = AboutCompany::factory()->create();

        $query = GraphQLQuery::query(self::QUERY)
            ->select(
                [
                    'id',
                    'video' => [
                        'id',
                        'name'
                    ]
                ]
            )->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'id',
                            'video',
                        ],
                    ]
                ]
            );
    }
}
