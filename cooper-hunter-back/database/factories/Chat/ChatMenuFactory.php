<?php

namespace Database\Factories\Chat;

use App\Enums\Chat\ChatMenuActionEnum;
use App\Enums\Chat\ChatMenuActionRedirectEnum;
use App\Models\Chat\ChatMenu;
use App\Models\Localization\Language;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|ChatMenu[]|ChatMenu create(array $attributes = [])
 */
class ChatMenuFactory extends BaseFactory
{
    protected $model = ChatMenu::class;

    public function definition(): array
    {
        return [
            'active' => true,
            'action' => ChatMenuActionEnum::ONLINE_CHAT(),
        ];
    }

    public function redirectAction(?ChatMenuActionRedirectEnum $redirectTo = null): self
    {
        return $this->state(
            [
                'action' => ChatMenuActionEnum::REDIRECT(),
                'redirect_to' => $redirectTo ?? ChatMenuActionRedirectEnum::FIND_SOLUTION()
            ]
        );
    }

    public function subMenuWithoutSubMenu(): self
    {
        return $this->state(
            [
                'action' => ChatMenuActionEnum::SUB_MENU()
            ]
        );
    }

    public function subMenu(): self
    {
        return $this->subMenuWithoutSubMenu()
            ->has(
                ChatMenu::factory()
                    ->count(3),
                'subMenu'
            );
    }

    public function configure(): self
    {
        return $this->afterCreating(
            fn(ChatMenu $chatMenu) => $chatMenu
                ->translations()
                ->createMany(
                    languages()
                        ->map(
                            fn(Language $language) => [
                                'language' => $language->slug,
                                'title' => $this->faker->text
                            ],
                        )
                        ->toArray()
                )
        );
    }
}
