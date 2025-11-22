<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Video\Link;

use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use App\GraphQL\Mutations\BackOffice\Catalog\Videos\Links\VideoLinkCreateMutation;
use App\Models\Catalog\Videos\Group;
use App\Models\Localization\Language;
use App\Permissions\Catalog\Videos\Link;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([Link\CreatePermission::KEY]);
    }

    public function test_success(): void
    {
        $group = Group::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VideoLinkCreateMutation::NAME)
                ->args([
                    'link_type' => VideoLinkTypeEnum::COMMON(),
                    'active' => true,
                    'group_id' => $group->id,
                    'link' => $this->faker->url,
                    'translations' => languages()->map(
                        fn(Language $language) => [
                            'title' => $this->faker->lexify,
                            'description' => $this->faker->text,
                            'language' => new EnumValue($language->slug)
                        ]
                    )
                        ->values()
                        ->toArray()
                ])
                ->select([
                    'id',
                    'active',
                    'link_type',
                    'group' => [
                        'id'
                    ],
                    'link'
                ])
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    VideoLinkCreateMutation::NAME => [
                        'id',
                        'link_type',
                        'active',
                        'group' => [
                            'id'
                        ],
                        'link'
                    ]
                ]
            ]);
    }

    /** @test */
    public function create_type_commercial(): void
    {
        $group = Group::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VideoLinkCreateMutation::NAME)
                ->args([
                    'link_type' => VideoLinkTypeEnum::COMMERCIAL(),
                    'active' => true,
                    'group_id' => $group->id,
                    'link' => $this->faker->url,
                    'translations' => languages()->map(
                        fn(Language $language) => [
                            'title' => $this->faker->lexify,
                            'description' => $this->faker->text,
                            'language' => new EnumValue($language->slug)
                        ]
                    )
                        ->values()
                        ->toArray()
                ])
                ->select([
                    'link_type',
                    'group' => [
                        'id'
                    ],
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    VideoLinkCreateMutation::NAME => [
                        'link_type' => VideoLinkTypeEnum::COMMERCIAL(),
                        'group' => [
                            'id' => $group->id
                        ],
                    ]
                ]
            ])
        ;
    }
}
