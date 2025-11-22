<?php

namespace Tests\Builders\Seo;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Seo\Models\Seo;
use App\Foundations\Modules\Seo\Services\SeoService;
use Illuminate\Http\UploadedFile;
use Tests\Builders\BaseBuilder;

class SeoBuilder extends BaseBuilder
{
    protected $file = null;

    function modelClass(): string
    {
        return Seo::class;
    }

    public function model(BaseModel $model): self
    {
        $this->data['model_id'] = $model->id;
        $this->data['model_type'] = $model::MORPH_NAME;
        return $this;
    }

    public function image(UploadedFile $file): self
    {
        $this->file = $file;
        return $this;
    }

    protected function afterSave($model): void
    {
        /** @var $model Seo */

        if($this->file){
            /** @var $service SeoService */
            $service = resolve(SeoService::class);
            $service->uploadImage($model, $this->file);
        }
    }

    protected function afterClear(): void
    {
        $this->file = null;
    }
}
