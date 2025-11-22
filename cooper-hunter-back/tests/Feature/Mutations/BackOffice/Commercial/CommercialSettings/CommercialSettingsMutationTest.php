<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\CommercialSettings;

use App\GraphQL\Mutations\BackOffice\Commercial\CommercialSettings\CommercialSettingsMutation;
use App\Models\Commercial\CommercialSettings;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CommercialSettingsMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialSettingsMutation::NAME;

    public function test_create(): void
    {
        $this->loginAsSuperAdmin();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(self::MUTATION)
                ->args(
                    [
                        'nextcloud_link' => $link = 'https://example.com'
                    ]
                )
                ->select(
                    [
                        'id',
                        'nextcloud_link',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'nextcloud_link' => $link
                        ],
                    ],
                ]
            );

        $this->assertDatabaseCount(CommercialSettings::TABLE, 1);
    }

    public function test_update(): void
    {
        $this->loginAsSuperAdmin();

        CommercialSettings::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(self::MUTATION)
                ->args(
                    [
                        'nextcloud_link' => $link = 'https://example.com'
                    ]
                )
                ->select(
                    [
                        'id',
                        'nextcloud_link',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'nextcloud_link' => $link
                        ],
                    ],
                ]
            );

        $this->assertDatabaseCount(CommercialSettings::TABLE, 1);
    }
}