<?php

namespace App\Foundations\Modules\Seo\Services;

use App\Foundations\Models\BaseModel;
use App\Foundations\Modules\Seo\Dto\SeoDto;
use App\Foundations\Modules\Seo\Models\Seo;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

class SeoService
{
    public function create(
        BaseModel $model,
        SeoDto $dto,
    ): Seo
    {
        $seo = $this->fill(new Seo(), $dto);
        $seo->model_id = $model->id;
        $seo->model_type = defined($model::class . '::MORPH_NAME')
            ? $model::MORPH_NAME
            : $model::class;

        $seo->save();

        if($dto->image){
            $this->uploadImage($seo, $dto->image);
        }

        return $seo;
    }

    public function update(
        Seo $model,
        SeoDto $dto,
    ): Seo
    {
        $model = $this->fill($model, $dto);

        $model->save();

        if($dto->image){
            $this->deleteImage($model);
            $this->uploadImage($model, $dto->image);
        }

        return $model;
    }

    public function fill(Seo $model, SeoDto $dto): Seo
    {
        $model->h1 = $dto->h1;
        $model->title = $dto->title;
        $model->keywords = $dto->keywords;
        $model->text = $dto->text;
        $model->desc = $dto->desc;

        return $model;
    }

    public function delete(Seo $model): bool
    {
        return $model->delete();
    }

    public function uploadImage(Seo $model, UploadedFile $file): Seo
    {
        $model = $this->deleteImage($model);
        $model->addImage($file);

        return $model;
    }

    public function deleteImage(Seo $model): Seo
    {
        $model->clearImageCollection();

        return $model;
    }

    public function deleteFile(Seo $model, int $mediaId = 0): void
    {
        if ($model->media->find($mediaId)) {

            $model->deleteMedia($mediaId);

            return;
        }

        throw new \Exception(__('exceptions.file.not_found'), Response::HTTP_NOT_FOUND);
    }
}
