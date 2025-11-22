<?php

namespace App\Services\About;

use App\Dto\About\Pages\PageDto;
use App\Dto\BaseTranslationDto;
use App\Dto\Orders\Dealer\PaymentDescDto;
use App\Dto\SimpleTranslationDto;
use App\Exceptions\About\CantDeletePageException;
use App\Exceptions\About\CantDisablePageException;
use App\Models\About\Page;
use App\Models\About\PageTranslation;
use App\Models\BaseModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PageService
{
    public function create(PageDto $dto): Page
    {
        return $this->fill($dto, new Page());
    }

    private function fill(PageDto $dto, Page $page): Page
    {
        $page->active = $dto->getActive();
        $page->slug = $dto->getSlug();

        $this->isAllowToDisablePage($page);

        $page->save();

        $this->saveTranslations($dto->getTranslations(), $page);

        return $page->refresh();
    }

    private function isAllowToDisablePage(Page $page): void
    {
        if (!$page->id || $page->active) {
            return;
        }

        if ($page->menus()
            ->where('active', true)
            ->exists()) {
            throw new CantDisablePageException();
        }
    }

    /**
     * @param BaseTranslationDto[] $translations
     * @param Page $page
     */
    private function saveTranslations(array $translations, Page $page): void
    {
        array_map(
            fn(BaseTranslationDto $dto) => $page
                ->translations()
                ->updateOrCreate(
                    [
                        'language' => $dto->getLanguage()
                    ],
                    [
                        'title' => $dto->getTitle(),
                        'description' => $dto->getDescription(),
                        'slug' => $dto->getSlug()
                    ]
                ),
            $translations
        );
    }

    public function update(PageDto $dto, Page $page): Page
    {
        return $this->fill($dto, $page);
    }

    public function updateOnlyDesc(PaymentDescDto $dto, Page $page): Page
    {
        foreach ($dto->translations as $translation){
            /** @var $translation SimpleTranslationDto */
            /** @var $t PageTranslation */
            $t = $page->translations()->where('language', $translation->getLanguage())->first();
            $t->description = $translation->getDescription();
            $t->save();
        }

        return $page->refresh();
    }

    public function toggleActive(Page $page): BaseModel
    {
        $page->active = !$page->active;

        $this->isAllowToDisablePage($page);

        $page->save();

        return $page;
    }

    public function delete(Page $page): bool
    {
        if ($page->menus()
            ->exists()) {
            throw new CantDeletePageException();
        }

        $page->delete();

        return true;
    }

    public function getList(array $args): LengthAwarePaginator
    {
        return Page::filter($args)
            ->page()
            ->orderByDesc('id')
            ->paginate(
                perPage: $args['per_page'],
                page: $args['page']
            );
    }
}
