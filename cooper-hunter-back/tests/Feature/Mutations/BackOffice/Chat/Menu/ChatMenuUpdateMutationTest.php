<?php


namespace Tests\Feature\Mutations\BackOffice\Chat\Menu;


use App\Enums\Chat\ChatMenuActionEnum;
use App\GraphQL\Mutations\BackOffice\Chat\Menu\ChatMenuUpdateMutation;
use App\Models\Chat\ChatMenu;
use App\Models\Localization\Language;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ChatMenuUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsSuperAdmin();
    }

    public function test_update_chat_menu(): void
    {
        $chatMenu = ChatMenu::factory()
            ->create();

        $subMenu = ChatMenu::factory()
            ->count(3)
            ->for($chatMenu, 'parent')
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(ChatMenuUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $chatMenu->id,
                        'chat_menu' => $chatMenu = [
                            'active' => true,
                            'action' => ChatMenuActionEnum::SUB_MENU(),
                            'sub_menu' => $subMenu->pluck('id')
                                ->toArray(),
                            'translations' => languages()
                                ->map(
                                    fn(Language $language) => [
                                        'language' => new EnumValue($language->slug),
                                        'title' => $this->faker->text
                                    ]
                                )
                                ->values()
                                ->toArray()
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'action',
                        'redirect_to',
                        'sub_menu' => [
                            'id'
                        ],
                        'active_sub_menu' => [
                            'id'
                        ],
                        'translation' => [
                            'id',
                            'language',
                            'title'
                        ],
                        'translations' => [
                            'id',
                            'language',
                            'title'
                        ],
                        'active',
                        'updated_at',
                        'updated_at'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        ChatMenuUpdateMutation::NAME => [
                            'action' => ChatMenuActionEnum::SUB_MENU,
                            'redirect_to' => null,
                            'sub_menu' => $subMenu
                                ->map(
                                    fn(ChatMenu $menu) => [
                                        'id' => $menu->id,
                                    ]
                                )
                                ->toArray(),
                            'active_sub_menu' => $subMenu
                                ->map(
                                    fn(ChatMenu $menu) => [
                                        'id' => $menu->id,
                                    ]
                                )
                                ->toArray(),
                            'translations' => array_map(
                                fn(array $item) => [
                                    'language' => (string)$item['language'],
                                    'title' => $item['title']
                                ],
                                $chatMenu['translations']
                            ),
                            'active' => true
                        ]
                    ]
                ]
            );
    }
}
