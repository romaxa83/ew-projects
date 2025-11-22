<?php

namespace App\GraphQL\Mutations\Admin\Car;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Media\File;
use App\Models\User\Car;
use App\Repositories\User\CarRepository;
use App\Services\Media\UploadService;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarService;
use GraphQL\Error\Error;

class CarDetachInsuranceFile extends BaseGraphQL
{
    public function __construct(
        private UploadService $uploadService,
        private CarRepository $repository,
        private CarService $service,
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Car
     */
    public function __invoke($_, array $args): Car
    {
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $car Car */
            $car = $this->repository->findByID($args['id']);

            if($car->insuranceFile){
                $this->uploadService->removeFile($car->insuranceFile);
                $this->service->setHasInsurance($car, false);
            }
            $car->refresh();

            // @todo dev-telegram
            TelegramDev::info("Админ отвязал страховку от авто ({$car->number})", $user->name);

            return $car;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
