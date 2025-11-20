<?php

namespace App\Repositories;

use App\Abstractions\AbstractRepository;
use App\Models\Page\Page;
use App\Models\Page\PageTranslation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PageRepository extends AbstractRepository
{
    public function query(): Builder
    {
        return Page::query();
    }

    public function getForHash(): Collection
    {
        return \DB::table(Page::TABLE)
            ->select([Page::TABLE.'.id','alias', 'active', 'name', 'text'])
            ->join(
                PageTranslation::TABLE,
                Page::TABLE.'.id',
                '=',
                PageTranslation::TABLE.'.page_id'
            )
            ->get()
            ;
    }

    public function disclaimerCurrentLocale(): ?Page
    {
        return $this->query()
            ->with(['current'])
            ->where('alias', Page::ALIAS_DISCLAIMER)
            ->first();
    }


    // todo , проверить если не где не используеться , то удалить
//    public function getByAlias(string $alias, array $relations = [])
//    {
//        return $this->query()
//            ->with($relations)
//            ->where('alias', $alias)
//            ->first();
//    }
}
