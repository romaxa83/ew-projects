<?php

namespace Feature\Mutations\BackOffice\Catalog\Troubleshoots\Group;

use App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Groups\TroubleshootGroupDeleteMutation;
use App\Models\Catalog\Troubleshoots\Group;
use App\Permissions\Catalog\Troubleshoots\Group as GroupPerm;
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
        $group = Group::factory()
            ->create();

        $this->loginByAdminManager([GroupPerm\DeletePermission::KEY]);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TroubleshootGroupDeleteMutation::NAME)
                ->args([
                    'id' => $group->id
                ])
                ->select([
                    'message',
                    'type'
                ])
                ->make()
        )
            ->assertJson([
                'data' => [
                    TroubleshootGroupDeleteMutation::NAME => [
                        'type' => MessageTypeEnum::SUCCESS,
                        'message' => __('messages.catalog.troubleshoots.group.actions.delete.success.one-entity')
                    ]
                ]
            ]);
    }
}


