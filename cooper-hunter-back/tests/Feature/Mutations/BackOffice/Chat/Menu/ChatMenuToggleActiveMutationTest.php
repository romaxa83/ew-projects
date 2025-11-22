<?php


namespace Tests\Feature\Mutations\BackOffice\Chat\Menu;


use App\GraphQL\Mutations\BackOffice\Chat\Menu\ChatMenuToggleActiveMutation;
use App\Models\Chat\ChatMenu;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChatMenuToggleActiveMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsSuperAdmin();
    }

    public function test_toggle_active_chat_menu(): void
    {
        $chatMenu = ChatMenu::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ChatMenuToggleActiveMutation::NAME)
                ->args(
                    [
                        'id' => $chatMenu->id
                    ]
                )
                ->select(
                    [
                        'id',
                        'active',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatMenuToggleActiveMutation::NAME => [
                            'id' => $chatMenu->id,
                            'active' => false
                        ]
                    ]
                ]
            );
    }
}
