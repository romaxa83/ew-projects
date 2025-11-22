<?php

namespace App\Services\Menu;

use App\Dto\BaseTranslationDto;
use App\Dto\Menu\MenuDto;
use App\Models\Admins\Admin;
use App\Models\BaseModel;
use App\Models\Menu\Menu;
use Illuminate\Database\Eloquent\Collection;

class MenuService
{
    /**
     * @param BaseTranslationDto[] $translations
     * @param Menu $menu
     */
    private function saveTranslations(array $translations, Menu $menu): void
    {
        array_map(
            fn(BaseTranslationDto $dto) => $menu
                ->translations()
                ->updateOrCreate(
                    [
                        'language' => $dto->getLanguage()
                    ],
                    [
                        'title' => $dto->getTitle(),
                    ]
                ),
            $translations
        );
    }

    private function checkMenuPageActive(Menu $menu): void
    {
        $menu->refresh();

        if (!$menu->active || $menu->page->active) {
            return;
        }

        $menu->page->active = true;
        $menu->page->save();
    }

    private function fill(MenuDto $dto, Menu $menu): Menu
    {
        $menu->page_id = $dto->getPageId();
        $menu->active = $dto->getActive();
        $menu->position = $dto->getPosition();
        $menu->block = $dto->getBlock();

        $menu->save();

        $this->checkMenuPageActive($menu);

        $this->saveTranslations($dto->getTranslations(), $menu);

        return $menu->refresh();
    }

    public function create(MenuDto $dto): Menu
    {
        return $this->fill($dto, new Menu());
    }

    public function update(MenuDto $dto, Menu $menu): Menu
    {
        return $this->fill($dto, $menu);
    }

    public function delete(Menu $menu): bool
    {
        return $menu->delete();
    }

    public function toggleActive(Menu $menu): BaseModel
    {
        $menu->active = !$menu->active;
        $menu->save();

        $this->checkMenuPageActive($menu);

        return $menu;
    }

    public function getList(array $args, array $relation, ?Admin $admin): Collection
    {
        return Menu::filter($args)
            ->forGuard($admin)
            ->with($relation)
            ->latest('sort')
            ->get();
    }
}
