<?php

namespace Feature\Mutations\BackOffice\Catalog\Video\Link;

use App\GraphQL\Mutations\BackOffice\Catalog\Videos\Links\VideoLinkToggleActiveMutation;
use App\Models\Catalog\Videos\VideoLink;
use App\Permissions\Catalog\Videos\Link;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class ToggleActiveTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;


    public function test_success(): void
    {
        $this->loginByAdminManager([Link\UpdatePermission::KEY]);

        $videoLink = VideoLink::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VideoLinkToggleActiveMutation::NAME)
                ->args([
                    'id' => $videoLink->id
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
                    VideoLinkToggleActiveMutation::NAME => [
                        'id' => $videoLink->id,
                        'active' => false
                    ]
                ]
            ]);
    }
}


