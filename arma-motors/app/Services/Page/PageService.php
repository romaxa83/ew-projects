<?php

namespace App\Services\Page;

use App\DTO\NameTranslationDTO;
use App\DTO\Page\PageDTO;
use App\DTO\Page\PageEditDTO;
use App\DTO\Page\PageTranslationDTO;
use App\Models\Page\Page;
use App\Models\Page\PageTranslation;
use App\Services\BaseService;
use DB;

class PageService extends BaseService
{

    public function __construct()
    {}

    public function create(PageDTO $dto): Page
    {
        DB::beginTransaction();
        try {

            $model = new Page();
            $model->alias = $dto->getAlias();

            $model->save();

            foreach ($dto->getTranslations() as $translation){
                /** @var $translation PageTranslationDTO */
                $t = new PageTranslation();
                $t->page_id = $model->id;
                $t->lang = $translation->getLang();
                $t->name = $translation->getName();
                $t->text = $translation->getText();
                $t->sub_text = $translation->getSubText();
                $t->save();
            }

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(PageEditDTO $dto, Page $model): Page
    {
        DB::beginTransaction();
        try {
            foreach ($dto->getTranslations() ?? [] as $translation){
                /** @var $translation PageTranslationDTO */
                /** @var $t PageTranslation */
                $t = $model->translations()->where('lang', $translation->getLang())->first();
                $t->name = $translation->getName();
                $t->text = $translation->getText();
                $t->sub_text = $translation->getSubText();

                $t->save();
            }

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}

