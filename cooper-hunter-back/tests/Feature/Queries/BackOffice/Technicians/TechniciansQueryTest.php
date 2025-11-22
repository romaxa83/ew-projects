<?php

namespace Tests\Feature\Queries\BackOffice\Technicians;

use App\GraphQL\Queries\BackOffice\Technicians\TechniciansQuery;
use App\Models\Technicians\Technician;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TechniciansQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = TechniciansQuery::NAME;

    public function test_get_technicians_list(): void
    {
        $this->loginAsSuperAdmin();

        Technician::factory()->times(20)->create();

        $query = sprintf(
            'query {
                %s {
                    data {
                        first_name
                        is_certified
                        hvac_license
                        epa_license
                        state {
                            name
                            short_name
                        }
                        email
                        lang
                    }
                }
            }',
            self::QUERY,
        );

        $this->postGraphQLBackOffice(compact('query'))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'data' => [
                                [
                                    'first_name',
                                    'is_certified',
                                    'hvac_license',
                                    'epa_license',
                                    'state' => [
                                        'name',
                                        'short_name',
                                    ],
                                    'email',
                                    'lang',
                                ]
                            ],
                        ],
                    ],
                ]
            );
    }
}
