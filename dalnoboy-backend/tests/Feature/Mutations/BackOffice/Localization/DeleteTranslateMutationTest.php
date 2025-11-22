<?php

namespace Tests\Feature\Mutations\BackOffice\Localization;

use App\GraphQL\Mutations\BackOffice\Localization\TranslateDeleteMutation;
use App\Models\Localization\Translate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class DeleteTranslateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public function test_delete_translate(): void
    {
        $this->loginAsAdminWithRole();

        $translate = Translate::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TranslateDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $translate->id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TranslateDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            Translate::class,
            [
                'id' => $translate->id
            ]
        );
    }
}
