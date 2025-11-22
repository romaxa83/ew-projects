<?php

namespace App\GraphQL\Mutations\Catalog\Calc;

use App\DTO\Catalog\Calc\Model\CalcModelDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Calc\CalcModel;
use App\Repositories\Catalog\Calc\CalcModelRepository;
use App\Repositories\Catalog\Car\BrandRepository;
use App\Services\Catalog\Calc\CalcModelService;
use App\Services\Telegram\TelegramDev;
use App\Traits\Validations\CalcValidate;
use GraphQL\Error\Error;

class CalcModelEdit extends BaseGraphQL
{
    use CalcValidate;

    public function __construct(
        protected CalcModelService $service,
        protected CalcModelRepository $repository,
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

            $model = $this->service->edit(
                CalcModelDTO::byArgs($args, $brand),
                $this->repository->findByID($args['id'])
            );

            // @todo dev-telegram
            TelegramDev::info("модель для калькулятора [{$model->id}] ({$model->model->name}) отредактирована", $user->name);

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
