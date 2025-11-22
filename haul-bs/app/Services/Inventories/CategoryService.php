<?php

namespace App\Services\Inventories;

use App\Dto\Inventories\CategoryDto;
use App\Events\Events\Inventories\Categories\CreateCategoryEvent;
use App\Events\Events\Inventories\Categories\DeleteCategoryEvent;
use App\Events\Events\Inventories\Categories\UpdateCategoryEvent;
use App\Foundations\Modules\Media\Models\Media;
use App\Foundations\Modules\Seo\Services\SeoService;
use App\Models\Inventories\Category;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;

final readonly class CategoryService
{
    public function __construct(protected SeoService $seoService)
    {}

    public function create(CategoryDto $dto): Category
    {
        return make_transaction(function () use($dto) {
            $model = $this->fill(new Category(), $dto);
            $model->active = true;
            $model->save();

            if($dto->imageMenu){
                $this->uploadImage($model, $dto->imageMenu, Category::IMAGE_MENU_FIELD_NAME);
            }
            if($dto->imageHeader){
                $this->uploadImage($model, $dto->imageHeader, Category::IMAGE_HEADER_FIELD_NAME);
            }
            if($dto->imageMobile){
                $this->uploadImage($model, $dto->imageMobile, Category::IMAGE_MOBILE_FIELD_NAME);
            }

            $this->seoService->create($model, $dto->seoDto);

            event(new CreateCategoryEvent($model));

            return $model;
        });
    }

    public function update(Category $model, CategoryDto $dto): Category
    {
        return make_transaction(function () use($model, $dto) {
            $model = $this->fill($model, $dto);

            $model->save();

            if($dto->imageMenu){
                $this->deleteImage($model, Category::IMAGE_MENU_FIELD_NAME);
                $this->uploadImage($model, $dto->imageMenu, Category::IMAGE_MENU_FIELD_NAME);
            }
            if($dto->imageHeader){
                $this->deleteImage($model, Category::IMAGE_HEADER_FIELD_NAME);
                $this->uploadImage($model, $dto->imageHeader, Category::IMAGE_HEADER_FIELD_NAME);
            }

            if($dto->imageMobile){
                $this->deleteImage($model, Category::IMAGE_MOBILE_FIELD_NAME);
                $this->uploadImage($model, $dto->imageMobile, Category::IMAGE_MOBILE_FIELD_NAME);
            }
            if($model->seo){
                $this->seoService->update($model->seo, $dto->seoDto);
            } else {
                $this->seoService->create($model, $dto->seoDto);
            }

            event(new UpdateCategoryEvent($model));

            return $model;
        });
    }

    protected function fill(Category $model, CategoryDto $dto): Category
    {
        $model->name = $dto->name;
        $model->slug = $dto->slug;
        $model->desc = $dto->desc;
        $model->parent_id = $dto->parentId;
        $model->display_menu = $dto->displayMenu;
        $model->position = $dto->position;

        return $model;
    }

    public function delete(Category $model): bool
    {
        if($model->seo){
            $model->seo->delete();
        }

        foreach ($model->inventoriesOnlyTrashed()->get() as $inventory){
            $inventory->update(['category_id' => null]);
        }

        $clone = clone $model;
        $res = $model->delete();

        if ($res) event(new DeleteCategoryEvent($clone));

        return $res;
    }

    public function uploadImage(
        Category $model,
        UploadedFile $file,
        string $collection
    ): Category
    {
        $model = $this->deleteImage($model, $collection);
        $model->addImage($file, $collection);

        return $model;
    }

    public function deleteImage(Category $model,  string $collection): Category
    {
        $model->clearImageCollection($collection);

        return $model;
    }

    public function deleteFile(Category $model, int $mediaId = 0): void
    {
        if ($media = Media::find($mediaId)) {

            $media->delete();

            return;
        }

        throw new \Exception(__('exceptions.file.not_found'), Response::HTTP_NOT_FOUND);
    }
}
