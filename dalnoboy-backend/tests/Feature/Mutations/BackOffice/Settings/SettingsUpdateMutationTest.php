<?php

namespace Tests\Feature\Mutations\BackOffice\Settings;

use App\GraphQL\Mutations\BackOffice\Settings\SettingsUpdateMutation;
use App\Models\Settings\Settings;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SettingsUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_update_settings(): void
    {
        $settingsData = [
            'email' => 'test@test.com',
            'phone' => '3455476767',
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SettingsUpdateMutation::NAME)
                ->args(
                    [
                        'settings' => $settingsData
                    ]
                )
                ->select(
                    [
                        'email',
                        'phone',
                    ]
                )
                ->make()
        )
            ->assertOk();

        $this->assertDatabaseHas(
            Settings::class,
            [
                'email' => 'test@test.com',
                'phone' => '3455476767',
            ]
        );
    }

    public function test_empty_phone(): void
    {
        $settingsData = [
            'email' => 'test@test.com',
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(SettingsUpdateMutation::NAME)
                ->args(
                    [
                        'settings' => $settingsData
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                        'title',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => 'Field SettingsInputType.phone of required type String! was not provided.'
                        ]
                    ]
                ]
            );
    }
}
