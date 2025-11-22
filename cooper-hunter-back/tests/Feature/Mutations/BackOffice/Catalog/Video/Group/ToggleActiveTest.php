<?php

namespace Feature\Mutations\BackOffice\Catalog\Video\Group;

use App\GraphQL\Mutations\BackOffice\Catalog\Videos\Groups\VideoGroupToggleActiveMutation;
use App\Models\Catalog\Videos;
use App\Permissions\Catalog\Videos\Group;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class ToggleActiveTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use WithFaker;

    public function test_success(): void
    {
        $this->loginByAdminManager([Group\UpdatePermission::KEY]);

        $videoGroup = Videos\Group::factory()->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(VideoGroupToggleActiveMutation::NAME)
                ->args([
                    'id' => $videoGroup->id,
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
                    VideoGroupToggleActiveMutation::NAME => [
                        'id' => $videoGroup->id,
                        'active' => false
                    ]
                ]
            ]);
    }
}


