<?php

namespace App\Services\Catalog\Calc;

use App\DTO\Catalog\Calc\model\CalcModelDTO;
use App\DTO\Catalog\Calc\Model\CalModelSparesDTO;
use App\DTO\Catalog\Calc\Model\CalModelWorkDTO;
use App\Helpers\ConvertNumber;
use App\Models\Catalogs\Calc\CalcModel;
use App\Models\Catalogs\Car\Brand;
use App\Repositories\Catalog\Calc\CalcModelRepository;
use App\Repositories\Catalog\Car\BrandRepository;
use App\Services\BaseService;
use App\Services\Telegram\TelegramDev;
use DB;
use function Symfony\Component\Translation\t;

class CalcModelService extends BaseService
{
    public function __construct(
        protected CalcModelRepository $calcModelRepository,
        protected BrandRepository $brandRepository
    )
    {}

    public function create(CalcModelDTO $dto): CalcModel
    {
        DB::beginTransaction();
        try {
            if($this->calcModelRepository->existModelByDTO($dto)){
                throw new \DomainException(__('error.calc_model.model exist'));
            }

            $model = new CalcModel();
            $model->brand_id = $dto->getBrandId();
            $model->model_id = $dto->getModelId();
            $model->mileage_id = $dto->getMileageId();
            $model->engine_volume_id = $dto->getEngineVolumeId();
            $model->drive_unit_id = $dto->getDriveUnitId();
            $model->transmission_id = $dto->getTransmissionId();
            $model->fuel_id = $dto->getFuelId();

            $model->save();

            $this->attachWorks($model, $dto);
            $this->attachSpares($model, $dto);

            DB::commit();

            return $model;
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    public function edit(CalcModelDTO $dto, CalcModel $model): CalcModel
    {
        DB::beginTransaction();
        try {

            if($this->calcModelRepository->existModelByDTO($dto, $model->id)){
                throw new \DomainException(__('error.calc_model.model exist'));
            }

            $model->brand_id = $dto->getBrandId();
            $model->model_id = $dto->getModelId();
            $model->mileage_id = $dto->getMileageId();
            $model->engine_volume_id = $dto->getEngineVolumeId();
            $model->drive_unit_id = $dto->getDriveUnitId();
            $model->transmission_id = $dto->getTransmissionId();
            $model->fuel_id = $dto->getFuelId();

            $model->save();

            $this->attachWorks($model, $dto);
            $this->attachSpares($model, $dto);

            DB::commit();

            return $model->refresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }

    private function attachWorks(CalcModel $model, CalcModelDTO $dto)
    {
        $brandsWorkIds = array_flip($this->brandRepository->getWorksId($dto->getBrandId()));
        $countW = 0;
        $model->works()->detach();
        foreach ($dto->getWorks() as $work){
            // привязываем те работы которые есть у бренда
            if(array_key_exists($work->getId(), $brandsWorkIds)){
                /** @var $work CalModelWorkDTO */
                $model->works()->attach($work->getId(), ['minutes' => ConvertNumber::fromFloatToNumber($work->getMinutes())]);
                $countW++;
            }
        }
        // @todo dev-telegram
        $cw = count($dto->getWorks());
        TelegramDev::info("🚗 модель для калькулятора [{$model->id}] , пришло РАБОТ - [{$cw}], привязано - [{$countW}]");
    }

    private function attachSpares(CalcModel $model, CalcModelDTO $dto)
    {
        $brandSparesId = array_flip($this->brandRepository->getSparesIdRelatedToBrand($dto->getBrandId()));
        $countS = 0;
        $model->spares()->detach();
        foreach ($dto->getSpares() as $spare){
            // привязываем те запчасти  у которых есть группа, и группа привязана к данному бренду
            if(array_key_exists($spare->getId(), $brandSparesId)){
                /** @var $spare CalModelSparesDTO */
                $model->spares()->attach($spare->getId(), ['qty' => ConvertNumber::fromFloatToNumber($spare->getQty())]);
                $countS++;
            }
        }
        // @todo dev-telegram
        $cs = count($dto->getSpares());
        TelegramDev::info("🚗 модель для калькулятора [{$model->id}] , пришло ЗАПЧАСТЕЙ - [{$cs}], привязано - [{$countS}]");
    }

    public function delete(CalcModel $model): void
    {
        DB::beginTransaction();
        try {
            $model->forceDelete();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error($e->getMessage());
            throw new \Exception($e->getMessage());
        }
    }
}

