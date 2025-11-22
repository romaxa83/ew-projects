<?php


namespace App\Services\Chat;


use App\Dto\Chat\Menu\ChatMenuDto;
use App\Models\Chat\ChatMenu;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Rebing\GraphQL\Support\SelectFields;

class ChatMenuService
{
    public function create(ChatMenuDto $dto): ChatMenu
    {
        return $this->editMenu($dto, new ChatMenu());
    }

    private function editMenu(ChatMenuDto $dto, ChatMenu $menu): ChatMenu
    {
        $menu->action = $dto->getAction();
        $menu->redirect_to = $dto->getRedirectTo();
        $menu->active = $dto->isActive();
        $menu->parent_id = $dto->getParentMenuItemId();

        $menu->save();

        ChatMenu::query()
            ->where('parent_id', $menu->id)
            ->update(['parent_id' => null]);

        if ($menu->action->isSubMenu() && $dto->isSetSubMenu()) {
            ChatMenu::query()
                ->whereIn('id', $dto->getSubMenu())
                ->update(['parent_id' => $menu->id]);
        }

        foreach ($dto->getTranslations() as $translation) {
            $menu
                ->translations()
                ->updateOrCreate(
                    [
                        'language' => $translation->getLanguage()
                    ],
                    [
                        'title' => $translation->getTitle()
                    ]
                );
        }

        return $menu->refresh();
    }

    public function update(ChatMenuDto $dto, ChatMenu $menu): ChatMenu
    {
        return $this->editMenu($dto, $menu);
    }

    public function delete(ChatMenu $menu): bool
    {
        return $menu->delete();
    }

    public function toggleActive(ChatMenu $menu): ChatMenu
    {
        $menu->active = !$menu->active;
        $menu->save();

        return $menu;
    }

    public function getForChat(array $args, SelectFields $fields): Collection
    {
        return ChatMenu::active()
            ->general()
            ->select($fields->getSelect() ?: ['id'])
            ->with($fields->getRelations())
            ->orderByDesc('sort')
            ->get();
    }

    public function list(array $args): LengthAwarePaginator
    {
        return ChatMenu::filter($args)
            ->paginate(
                perPage: $args['per_page'],
                page: $args['page']
            );
    }
}
