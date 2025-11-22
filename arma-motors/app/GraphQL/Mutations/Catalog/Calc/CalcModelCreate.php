<?php

namespace App\GraphQL\Mutations\Catalog\Calc;

use App\DTO\Catalog\Calc\Model\CalcModelDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Calc\CalcModel;
use App\Repositories\Catalog\Car\BrandRepository;
use App\Services\Catalog\Calc\CalcModelService;
use App\Services\Telegram\TelegramDev;
use App\Traits\Validations\CalcValidate;
use GraphQL\Error\Error;

class CalcModelCreate extends BaseGraphQL
{
    use CalcValidate;

    public function __construct(
        protected CalcModelService $service,
        protected BrandRepository $brandRepository
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return CalcModel
     */
    public function __invoke($_, array $args): CalcModel
    {
        $user = \Auth::guard(Admin::GUARD)->user();
        try {

            $brand = $this->brandRepository->findByID($args['brandId']);

            $this->validate($args, $brand);

            $model = $this->service->create(
                CalcModelDTO::byArgs($args, $brand)
            );

            // @todo dev-telegram
            TelegramDev::info("🚗 модель для калькулятора [{$model->id}] на основе модели ({$model->brand->name} - {$model->model->name}) СОЗДАНА", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
