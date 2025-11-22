<?php

namespace Feature\Mutations\BackOffice\Catalog\Video\Group;

use App\GraphQL\Mutations\BackOffice\Catalog\Videos\Groups\VideoGroupUpdateMutation;
use App\Models\Catalog\Videos;
use App\Models\Localization\Language;
use App\Permissions\Catalog\Videos\Group;
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

    public function test_success(): void
    {
        $this->loginByAdminManager([Group\UpdatePermission::KEY]);

        $videoGroup = Videos\Group::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VideoGroupUpdateMutation::NAME)
                ->args([
                    'id' => $videoGroup->id,
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
                ])
                ->select([
                    'id',
                    'active'
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    VideoGroupUpdateMutation::NAME => [
                        'id' => $videoGroup->id,
                        'active' => false
                    ]
                ]
            ]);
    }
}

