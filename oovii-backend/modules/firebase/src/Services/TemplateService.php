<?php

namespace WezomCms\Firebase\Services;

use Illuminate\Support\Facades\DB;
use WezomCms\Firebase\Dto\TemplateDto;
use WezomCms\Firebase\Dto\TemplateTranslationDto;
use WezomCms\Firebase\Models\Template;
use WezomCms\Firebase\Models\TemplateTranslation;

class TemplateService
{
    public function create(TemplateDto $dto): Template
    {
        DB::beginTransaction();
        try {
            $model = new Template();
            $model->active = $dto->active;
            $model->type = $dto->type;
            $model->vars = $dto->vars;
            $model->save();

            foreach ($dto->translations as $item){
                /** @var $item TemplateTranslationDto */
                $t = new TemplateTranslation();
                $t->template_id = $model->id;
                $t->locale = $item->locale;
                $t->title = $item->title;
                $t->text = $item->text;
                $t->save();
            }

            DB::commit();

            return $model;
        } catch(\Exception $exception) {
            DB::rollBack();
            \Log::error($exception->getMessage());
            throw new \Exception($exception->getMessage());
        }
    }
}
