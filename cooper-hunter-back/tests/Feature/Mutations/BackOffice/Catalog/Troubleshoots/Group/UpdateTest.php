<?php

namespace Feature\Mutations\BackOffice\Catalog\Troubleshoots\Group;

use App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Groups\TroubleshootGroupUpdateMutation;
use App\Models\Catalog\Troubleshoots;
use App\Models\Localization\Language;
use App\Permissions\Catalog\Troubleshoots\Group;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([Group\UpdatePermission::KEY]);
    }

    public function test_success(): void
    {
        $group = Troubleshoots\Group::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TroubleshootGroupUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $group->id,
                        'active' => false,
                        'translations' => languages()
                            ->map(
                                fn (Language $language) => [
                                    'language' => new EnumValue($language->slug),
                                    'title' => $this->faker->lexify,
                                    'description' => $this->faker->text
                                ]
                            )
                            ->values()
                            ->toArray()
                    ]
                )
                ->select([
                    'id',
                    'active',
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    TroubleshootGroupUpdateMutation::NAME => [
                        'id' => $group->id,
                        'active' => false,
                    ]
                ]
            ]);
    }
}

