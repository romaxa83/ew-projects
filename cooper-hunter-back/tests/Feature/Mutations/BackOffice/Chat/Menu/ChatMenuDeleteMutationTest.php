<?php


namespace Tests\Feature\Mutations\BackOffice\Chat\Menu;


use App\GraphQL\Mutations\BackOffice\Chat\Menu\ChatMenuDeleteMutation;
use App\Models\Chat\ChatMenu;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChatMenuDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsSuperAdmin();
    }

    public function test_delete_chat_menu(): void
    {
        $chatMenu = ChatMenu::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ChatMenuDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $chatMenu->id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatMenuDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            ChatMenu::class,
            [
                'id' => $chatMenu->id
            ]
        );
    }
}
