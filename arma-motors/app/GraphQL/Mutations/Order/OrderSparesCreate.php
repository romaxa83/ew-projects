<?php

namespace App\GraphQL\Mutations\Order;

use App\DTO\Order\OrderSparesDTO;
use App\Events\Order\CreateOrder;
use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Helpers\Logger\OrderLogger;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Repositories\Catalog\Service\ServiceRepository;
use App\Repositories\Order\RecommendationRepository;
use App\Repositories\User\CarRepository;
use App\Services\Order\OrderService;
use App\Services\Order\RecommendationService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class OrderSparesCreate extends BaseGraphQL
{
    public function __construct(
        protected ServiceRepository $serviceRepository,
        protected OrderService $service,
        protected CarRepository $carRepository,
        protected RecommendationRepository $recommendationRepository,
        protected RecommendationService $recommendationService,
    )
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return Order
     */
    public function __invoke($_, array $args): Order
    {
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {
            $car = $this->carRepository->findByID($args['carId'], ['brand']);

            if(!$car->brand->isMain()){
                throw new \InvalidArgumentException(
                    __('error.order.order not support brand', ['brand' => $car->brand->name]),
                    ErrorsCode::BAD_REQUEST
                );
            }

            $service = $this->serviceRepository->getSparesService();
            $args['serviceId'] = $service->id;

            $dto = OrderSparesDTO::byArgs($args);
            $order = $this->service->createSpares($dto, $user);

            OrderLogger::info('CREATE [spares] by data', $args);
            if($dto->getRecommendationId()){
                $this->recommendationService->setUsedStatusFromID($dto->getRecommendationId());
                OrderLogger::info('TOGGLE the "used" status of a recommendation');
            }

            event(new CreateOrder($order));
            OrderLogger::info('EVENT [create order]');

            TelegramDev::info("Пользователь создал заявку на SPARES", $user->name);

            return $order;
        } catch (\Throwable $e) {
            OrderLogger::error("CREATE order has a error [{$e->getMessage()}]", $e->getTrace());
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

