<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Video\Link;

use App\GraphQL\Mutations\BackOffice\Catalog\Videos\Links\VideoLinkDeleteMutation;
use App\Models\Catalog\Videos\VideoLink;
use App\Permissions\Catalog\Videos\Link as LinkPerm;
use Core\Enums\Messages\MessageTypeEnum;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([LinkPerm\DeletePermission::KEY]);
    }

    public function test_success(): void
    {
        $videoLink = VideoLink::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VideoLinkDeleteMutation::NAME)
                ->args([
                    'id' => $videoLink->id
                ])
                ->select([
                    'message',
                    'type'
                ])
                ->make()
        )
            ->assertOk()
            ->assertJson([
                'data' => [
                    VideoLinkDeleteMutation::NAME => [
                        'type' => MessageTypeEnum::SUCCESS,
                        'message' => __('messages.catalog.video.link.actions.delete.success.one-entity')
                    ]
                ]
            ]);
    }
}


