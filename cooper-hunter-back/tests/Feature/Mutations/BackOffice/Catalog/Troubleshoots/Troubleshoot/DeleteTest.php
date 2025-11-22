<?php

namespace Feature\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot;

use App\GraphQL\Mutations\BackOffice\Catalog\Troubleshoots\Troubleshoot\TroubleshootDeleteMutation;
use App\Models\Catalog\Troubleshoots\Troubleshoot;
use App\Permissions\Catalog\Troubleshoots\Troubleshoot as TroubleshootPerm;
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
        $troubleshoot = Troubleshoot::factory()
            ->create();

        $this->loginByAdminManager([TroubleshootPerm\DeletePermission::KEY]);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TroubleshootDeleteMutation::NAME)
                ->args([
                    'id' => $troubleshoot->id
                ])
                ->select([
                    'message',
                    'type'
                ])
                ->make()
        )
            ->assertJson([
                'data' => [
                    TroubleshootDeleteMutation::NAME => [
                        'message' => __('messages.catalog.troubleshoots.troubleshoot.actions.delete.success.one-entity'),
                        'type' => MessageTypeEnum::SUCCESS,
                    ]
                ]
            ]);
    }
}


