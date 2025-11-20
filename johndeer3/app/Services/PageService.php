<?php

namespace App\Services;

use App\Abstractions\AbstractService;
use App\DTO\Page\PageDto;
use App\DTO\SimpleTranslationDto;
use App\Models\Page\Page;
use App\Models\Page\PageTranslation;
use DB;

class PageService extends AbstractService
{
    public function create(PageDto $dto): Page
    {
        $model = new Page();
        DB::transaction(function() use($model, $dto) {
            $model->alias = $dto->type;
            $model->active = $dto->active;
            $model->save();

            foreach ($dto->getTranslations() as $item) {
                /** @var $item SimpleTranslationDto */
                $t = new PageTranslation();
                $t->lang = $item->lang;
                $t->name = $item->name;
                $t->text = $item->text;
                $t->page_id = $model->id;
                $t->save();
            }
        });

        return $model;
    }


    public function update(Page $model, array $data): Page
    {
        DB::transaction(function() use($model, $data) {
            foreach ($data['name'] ?? [] as $lang => $name) {
                $t = $model->translations->where('lang', $lang)->first();
                if($t){
                    $t->name = $name;
                    $t->save();
                }
            }

            foreach ($data['text'] ?? [] as $lang => $text) {
                $t = $model->translations->where('lang', $lang)->first();
                if($t){
                    $t->text = $text;
                    $t->save();
                }
            }
        });

        return $model;
    }
}
