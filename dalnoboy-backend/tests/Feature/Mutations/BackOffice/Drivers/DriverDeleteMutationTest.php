<?php

namespace Tests\Feature\Mutations\BackOffice\Drivers;

use App\Enums\Utilities\MorphModelNameEnum;
use App\GraphQL\Mutations\BackOffice\Drivers\DriverDeleteMutation;
use App\Models\Drivers\Driver;
use App\Models\Phones\Phone;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DriverDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();
    }

    public function test_delete_driver(): void
    {
        $driver = Driver::factory()
            ->create()
            ->first();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(DriverDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $driver->id,
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        DriverDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Phone::class,
            [
                'owner_id' => $driver->id,
                'owner_type' => MorphModelNameEnum::driver()->key
            ]
        );
    }
}
