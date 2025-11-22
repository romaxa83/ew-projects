<?php

namespace Feature\Mutations\BackOffice\Catalog\Video\Group;

use App\GraphQL\Mutations\BackOffice\Catalog\Videos\Groups\VideoGroupCreateMutation;
use App\Models\Localization\Language;
use App\Permissions\Catalog\Videos\Group;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use AdminManagerHelperTrait;

    public function test_success(): void
    {
        $this->loginByAdminManager([Group\CreatePermission::KEY]);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VideoGroupCreateMutation::NAME)
                ->args([
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
                    'active',
                    'translations' => [
                        'language',
                        'title',
                        'description'
                    ]
                ])
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    VideoGroupCreateMutation::NAME => [
                        'id',
                        'active',
                        'translations' => [
                            '*' => [
                                'language',
                                'title',
                                'description'
                            ]
                        ]
                    ]
                ]
            ]);
    }
}
