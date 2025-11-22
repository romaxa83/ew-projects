<?php

namespace App\GraphQL\Mutations\User\User;

use App\DTO\User\CarDTO;
use App\GraphQL\BaseGraphQL;
use App\Models\User\Car;
use App\Models\User\User;
use App\Repositories\User\CarRepository;
use App\Services\AA\RequestService;
use App\Services\Telegram\TelegramDev;
use App\Services\User\CarService;
use GraphQL\Error\Error;

class UserAddCars extends BaseGraphQL
{
    public function __construct(
        protected CarService $carService,
        protected CarRepository $carRepository,
        protected RequestService $requestService,
    ){}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return User
     */
    public function __invoke($_, array $args): User
    {
        /** @var $user User*/
        $user = \Auth::guard(User::GUARD)->user();
        try {
            foreach ($args["input"] ?? [] as $item){
                $dto = CarDTO::byArgs($item);
                $dto->setIsAddToApp();

                if($this->carRepository->getByNumber($dto->getNumber())){
                    throw new \DomainException(__('error.car with number exist', ['number' => $dto->getNumber()]));
                }

                if($this->carRepository->getByNumberAndUserToArchive($dto->getNumber(), $user->id)){
                    throw new \DomainException(__('error.car exist to archive'));
                }
                /** @var $model Car */
                $model = $this->carService->create($dto, $user);

                // отправляем запрос в AA если у пользователя есть uuid (соответственно он есть в системе АА)
                if($user->uuid){
                    $this->requestService->getCarFromAA($model);
                }

                // @todo dev-telegram
                TelegramDev::info("Пользователь - ({$user->name}) , добавил авто ({$dto->getNumber()})");
            }

            $user->refresh();

            return $user;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

