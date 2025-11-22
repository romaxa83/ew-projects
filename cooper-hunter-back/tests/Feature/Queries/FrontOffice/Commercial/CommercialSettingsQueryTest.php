<?php

namespace Tests\Feature\Queries\FrontOffice\Commercial;

use App\GraphQL\Queries\FrontOffice\Commercial\CommercialSettingsQuery;
use App\Models\Commercial\CommercialSettings;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CommercialSettingsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = CommercialSettingsQuery::NAME;

    public function test_get_list(): void
    {
        $this->loginAsTechnicianWithRole();

        CommercialSettings::factory()->create();

        $this->postGraphQL(
            GraphQLQuery::query(self::QUERY)
                ->select(
                    [
                        'nextcloud_link',
                        'pdf' => [
                            'id',
                        ],
                        'rdp' => [
                            'id'
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            'nextcloud_link',
                            'pdf',
                            'rdp',
                        ],
                    ],
                ]
            );
    }
}