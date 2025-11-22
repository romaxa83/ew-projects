<?php

namespace App\GraphQL\Mutations\Order;

use App\DTO\Order\OrderServiceDTO;
use App\Events\Order\CreateOrder;
use App\GraphQL\BaseGraphQL;
use App\Helpers\Logger\OrderLogger;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Repositories\Order\RecommendationRepository;
use App\Services\Order\OrderService;
use App\Services\Order\RecommendationService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class OrderServiceCreate extends BaseGraphQL
{
    public function __construct(
        protected OrderService $service,
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
            $this->validation($args, $this->rules());

            $dto = OrderServiceDTO::byArgs($args);

            $order = $this->service->createService($dto, $user);

            OrderLogger::info('CREATE [service] by data', $args);
            if($dto->getRecommendationId()){
                $this->recommendationService->setUsedStatusFromID($dto->getRecommendationId());
                OrderLogger::info('TOGGLE the "used" status of a recommendation');
            }

            event(new CreateOrder($order));
            OrderLogger::info('EVENT [create order]');

            TelegramDev::info("Пользователь создал заявку на сервис", $user->name, TelegramDev::LEVEL_IMPORTANT);

            return $order;
        } catch (\Throwable $e) {
            OrderLogger::error("CREATE order has a error [{$e->getMessage()}]", $e->getTrace());
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }

    private function rules(): array
    {
        return [
            'date' => ['required', 'numeric', 'digits:13'],
            'time' => ['required', 'numeric', 'digits_between:6,8'],
        ];
    }
}
