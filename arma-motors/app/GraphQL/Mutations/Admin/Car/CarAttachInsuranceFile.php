<?php

namespace App\GraphQL\Mutations\Admin\Car;

use App\DTO\Media\FileDTO;
use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Media\File;
use App\Models\User\Car;
use App\Repositories\User\CarRepository;
use App\Services\Media\UploadService;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarService;
use GraphQL\Error\Error;

class CarAttachInsuranceFile extends BaseGraphQL
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
     * @return File
     */
    public function __invoke($_, array $args): File
    {
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $car Car */
            $car = $this->repository->findByID($args['modelId']);

            if(null !== $car->insuranceFile){
                throw new \DomainException(__('error.car have insurance'), ErrorsCode::BAD_REQUEST);
            }

            $args['model'] = File::MODEL_CAR;
            $args['type'] = Car::FILE_INSURANCE_TYPE;

            $dto = FileDTO::byArgs($args);

            $this->uploadService->uploadFile($dto);
            $this->service->setHasInsurance($car, true);

            $car->refresh();

            // @todo dev-telegram
            TelegramDev::info("Админ привязал страховку к авто ({$car->number}) ", $user->name);

            return $car->insuranceFile;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name, TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
