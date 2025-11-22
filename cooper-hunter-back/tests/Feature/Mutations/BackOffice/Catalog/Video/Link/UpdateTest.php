<?php

namespace Feature\Mutations\BackOffice\Catalog\Video\Link;

use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use App\GraphQL\Mutations\BackOffice\Catalog\Videos\Links\VideoLinkUpdateMutation;
use App\Models\Catalog\Videos\Group;
use App\Models\Catalog\Videos\VideoLink;
use App\Models\Catalog\Videos\VideoLinkTranslation;
use App\Permissions\Catalog\Videos\Link;
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

        $this->loginByAdminManager([Link\UpdatePermission::KEY]);
    }

    public function test_success(): void
    {
        $group = Group::factory()->create();

        $videoLink = VideoLink::factory()->create();

        $link = $this->faker->url;

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VideoLinkUpdateMutation::NAME)
                ->args([
                    'id' => $videoLink->id,
                    'active' => false,
                    'link_type' => $linkType = VideoLinkTypeEnum::SUPPORT(),
                    'link' => $link,
                    'group_id' => $group->id,
                    'translations' => $videoLink->translations->map(
                        fn(VideoLinkTranslation $translation) => [
                            'language' => new EnumValue($translation->language),
                            'title' => $translation->title,
                            'description' => $translation->description
                        ]
                    )->toArray()
                ])
                ->select([
                    'id',
                    'active',
                    'link',
                    'link_type',
                    'group' => [
                        'id'
                    ]
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    VideoLinkUpdateMutation::NAME => [
                        'id' => $videoLink->id,
                        'active' => false,
                        'link' => $link,
                        'link_type' => $linkType->value,
                        'group' => [
                            'id' => $group->id
                        ]
                    ]
                ]
            ]);
    }
}

