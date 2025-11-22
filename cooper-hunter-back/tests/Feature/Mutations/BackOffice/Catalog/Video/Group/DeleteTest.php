<?php

namespace Feature\Mutations\BackOffice\Catalog\Video\Group;

use App\GraphQL\Mutations\BackOffice\Catalog\Videos\Groups\VideoGroupDeleteMutation;
use App\Models\Catalog\Videos\Group;
use App\Permissions\Catalog\Videos\Group as GroupPerm;
use Core\Enums\Messages\MessageTypeEnum;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    public function test_success(): void
    {
        $this->loginByAdminManager([GroupPerm\DeletePermission::KEY]);

        $videoGroup = Group::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VideoGroupDeleteMutation::NAME)
                ->args([
                    'id' => $videoGroup->id,
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
                    VideoGroupDeleteMutation::NAME => [
                        'message' => __('messages.catalog.video.group.actions.delete.success.one-entity'),
                        'type' => MessageTypeEnum::SUCCESS
                    ]
                ]
            ]);
    }
}

