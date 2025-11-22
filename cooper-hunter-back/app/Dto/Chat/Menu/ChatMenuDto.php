<?php


namespace App\Dto\Chat\Menu;


use App\Enums\Chat\ChatMenuActionEnum;
use App\Enums\Chat\ChatMenuActionRedirectEnum;

class ChatMenuDto
{
    private ChatMenuActionEnum $action;
    private ?ChatMenuActionRedirectEnum $redirectTo;
    private ?array $subMenu;
    private ?int $parentMenuItemId;
    private bool $active;
    private array $translations;

    public static function byArgs(array $args): self
    {
        $chatMenu = new self();

        $chatMenu->action = ChatMenuActionEnum::fromValue($args['action']);
        $chatMenu->redirectTo = $chatMenu->action->isRedirect() ? ChatMenuActionRedirectEnum::fromValue(
            $args['redirect_to']
        ) : null;
        $chatMenu->subMenu = $chatMenu->action->isSubMenu() && !empty($args['sub_menu']) ? array_values(
            array_unique($args['sub_menu'])
        ) : null;

        $chatMenu->parentMenuItemId = $args['parent_menu_item_id'] ?? null;

        $chatMenu->active = $args['active'];

        foreach ($args['translations'] as $translation) {
            $chatMenu->translations[] = ChatMenuTranslationDto::byArgs($translation);
        }

        return $chatMenu;
    }


    public function getAction(): ChatMenuActionEnum
    {
        return $this->action;
    }

    public function getRedirectTo(): ?ChatMenuActionRedirectEnum
    {
        return $this->redirectTo;
    }

    public function getSubMenu(): ?array
    {
        return $this->subMenu;
    }

    public function isSetSubMenu(): bool
    {
        return isset($this->subMenu);
    }

    public function getParentMenuItemId(): ?int
    {
        return $this->parentMenuItemId;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @return ChatMenuTranslationDto[]
     */
    public function getTranslations(): array
    {
        return $this->translations;
    }
}
