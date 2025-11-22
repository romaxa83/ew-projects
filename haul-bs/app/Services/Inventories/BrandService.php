<?php

namespace App\Services\Inventories;

use App\Dto\Inventories\BrandDto;
use App\Events\Events\Inventories\Brands\CreateBrandEvent;
use App\Events\Events\Inventories\Brands\DeleteBrandEvent;
use App\Events\Events\Inventories\Brands\UpdateBrandEvent;
use App\Foundations\Modules\Seo\Services\SeoService;
use App\Models\Inventories\Brand;

final readonly class BrandService
{
    public function __construct(protected SeoService $seoService)
    {}

    public function create(BrandDto $dto): Brand
    {
        return make_transaction(function () use($dto) {
            $model = $this->fill(new Brand(), $dto);

            $model->save();

            $this->seoService->create($model, $dto->seoDto);

            event(new CreateBrandEvent($model));

            return $model;
        });
    }

    public function update(Brand $model, BrandDto $dto): Brand
    {
        return make_transaction(function () use($model, $dto) {
            $model = $this->fill($model, $dto);

            $model->save();

            if($model->seo){
                $this->seoService->update($model->seo, $dto->seoDto);
            } else {
                $this->seoService->create($model, $dto->seoDto);
            }

            event(new UpdateBrandEvent($model));

            return $model;
        });
    }

    protected function fill(Brand $model, BrandDto $dto): Brand
    {
        $model->name = $dto->name;
        $model->slug = $dto->slug;

        return $model;
    }

    public function delete(Brand $model): bool
    {
        if($model->seo){
            $model->seo->delete();
        }

        $clone = clone $model;
        $res = $model->delete();

        if ($res) event(new DeleteBrandEvent($clone));

        return $res;
    }
}
