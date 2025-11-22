<?php

namespace Tests\Builders\Inventories;

use App\Models\Inventories\Category;
use App\Services\Inventories\CategoryService;
use Illuminate\Http\UploadedFile;
use Tests\Builders\BaseBuilder;

class CategoryBuilder extends BaseBuilder
{
    protected $headerImg = null;
    protected $menuImg = null;
    protected $mobileImg = null;

    function modelClass(): string
    {
        return Category::class;
    }

    public function name(string $value): self
    {
        $this->data['name'] = $value;
        return $this;
    }

    public function slug(string $value): self
    {
        $this->data['slug'] = $value;
        return $this;
    }

    public function parent(Category $model): self
    {
        $this->data['parent_id'] = $model->id;
        return $this;
    }

    public function position(int $value): self
    {
        $this->data['position'] = $value;
        return $this;
    }

    public function headerImg(UploadedFile $file): self
    {
        $this->headerImg = $file;
        return $this;
    }

    public function menuImg(UploadedFile $file): self
    {
        $this->menuImg = $file;
        return $this;
    }

    public function mobileImg(UploadedFile $file): self
    {
        $this->mobileImg = $file;
        return $this;
    }

    protected function afterSave($model): void
    {
        /** @var $model Category */

        if($this->headerImg){
            /** @var $service CategoryService */
            $service = resolve(CategoryService::class);
            $service->uploadImage($model, $this->headerImg, Category::IMAGE_HEADER_FIELD_NAME);
        }
        if($this->menuImg){
            /** @var $service CategoryService */
            $service = resolve(CategoryService::class);
            $service->uploadImage($model, $this->menuImg, Category::IMAGE_MENU_FIELD_NAME);
        }
        if($this->mobileImg){
            /** @var $service CategoryService */
            $service = resolve(CategoryService::class);
            $service->uploadImage($model, $this->mobileImg, Category::IMAGE_MOBILE_FIELD_NAME);
        }
    }

    protected function afterClear(): void
    {
        $this->menuImg = null;
        $this->headerImg = null;
        $this->mobileImg = null;
    }
}
