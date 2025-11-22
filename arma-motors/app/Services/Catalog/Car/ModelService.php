<?php

namespace App\Services\Catalog\Car;

use App\DTO\Catalog\Car\ModelEditDTO;
use App\Events\ChangeHashEvent;
use App\Helpers\Logger\AALogger;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\Hash;
use App\Repositories\Catalog\Car\BrandRepository;
use App\Repositories\Catalog\Car\ModelRepository;
use App\Services\Telegram\TelegramDev;
use DB;

class ModelService
{
    public function __construct(
        protected BrandRepository $brandRepository,
        protected ModelRepository $modelRepository
    )
    {}

    public function importFromAA(array $data)
    {
        $allItemCount = 0;
        $createBrand = 0;
        $updateBrand = 0;
        $createModel = 0;
        $updateModel = 0;
        DB::beginTransaction();
        try {
            foreach ($data ?? [] as $item){
                if(null == $item['brandId'] || null == $item['brandName'] ){
                    AALogger::info('ERROR ', $item);
                    throw new \InvalidArgumentException("Wrong data");
                }

                /** @var $brand Brand */
                $brand = $this->brandRepository->getOneBy('name', $item['brandName']);
                if($brand){
                    if($item['brandId'] !== $brand->uuid){
                        $brand->uuid = $item['brandId'];
                        $brand->save();
                        $updateBrand++;
                    }

                } else {
                    $brand = new Brand();
                    $brand->uuid = $item['brandId'];
                    $brand->name = $item['brandName'];
                    if(Brand::isMainFromName($item['brandName'])){
                        $brand->is_main = true;
                    }
                    $brand->save();
                    $createBrand++;
                }

                if(null == $item['modelId'] || null == $item['modelName'] ){
                    AALogger::info('ERROR ', $item);
                    throw new \InvalidArgumentException("Wrong data");
                }

                $model = Model::query()
                    ->whereHas('brand', function ($q) use ($item){
                        $q->where('name', $item['brandName']);
                    })
                    ->where('name', $item['modelName'])
                    ->first();

                if($model){
                    if($item['modelId'] !== $model->uuid){
                        $model->uuid = $item['modelId'];
                        $model->save();
                        $updateModel++;
                    }
                } else {
                    $model = new Model();
                    $model->uuid = $item['modelId'];
                    $model->name = $item['modelName'];
                    $model->brand_id = $brand->id;

                    $model->save();
                    $createModel++;
                }
                $allItemCount++;
            }

            TelegramDev::info("Create brand - {$createBrand} \n Updated brand - {$updateBrand} \n, Create model - {$createModel} \n Updated model - {$updateModel} \n Total - {$allItemCount}", TelegramDev::LEVEL_CRITICAL);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(ModelEditDTO $dto, Model $model): Model
    {
        try {

            $model->active = $dto->changeActive() ? $dto->getActive() : $model->active;
            $model->sort = $dto->changeSort() ? $dto->getSort() : $model->sort;
            $model->name = $dto->changeName() ? $dto->getName() : $model->name;
            $model->brand_id = $dto->changeBrandId() ? $dto->getBrandId() : $model->brand_id;
            $model->for_credit = $dto->changeForCredit() ? $dto->getForCredit() : $model->for_credit;
            $model->for_calc = $dto->changeForCalc() ? $dto->getForCalc() : $model->for_calc;

            $model->save();

            event(new ChangeHashEvent(Hash::ALIAS_MODEL));

            return $model;
        } catch (\Throwable $e) {

            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function toggleActive(Model $model): Model
    {
        try {
            $model->active = !$model->active;
            $model->save();

            event(new ChangeHashEvent(Hash::ALIAS_MODEL));

            return $model;
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}
