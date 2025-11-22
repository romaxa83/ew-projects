<?php

namespace App\GraphQL\Mutations\Order;

use App\DTO\Order\OrderServiceDTO;
use App\Events\Order\AcceptAgreementEvent;
use App\GraphQL\BaseGraphQL;
use App\Models\Agreement\Agreement;
use App\Models\Catalogs\Service\Service;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Repositories\Order\AgreementRepository;
use App\Services\Order\AgreementService;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class AgreementAccept extends BaseGraphQL
{
    public function __construct(
        private AgreementRepository $repo,
        private AgreementService $service,
        private OrderService $serviceOrder,
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
    public function __invoke($_, array $args): Agreement
    {
        /** @var $user User */
        $user = \Auth::guard(User::GUARD)->user();
        try {
            /** @var $model Agreement */
            $model = $this->repo->findByID($args['id'],['car']);
            $this->service->setStatus($model, Agreement::STATUS_USED);

//            $order = $model->baseOrder;
//            if(!$order){
//                $service = Service::query()->where('alias', Service::SERVICE_ALIAS)->first();
//                if(!$model->car){
//                    throw new \Exception("Not found car row - [uuid - {$model->car_uuid}]");
//                }
//
//                $dto = OrderServiceDTO::byArgs([
//                    'serviceId' => $service->id,
//                    'dealershipId' => $args['dealershipId'],
//                    'carId' => $model->car->id,
//                    'agreementId' => $model->id,
//                    'communication' => $args['communication'],
//                    'uuid' => $model->uuid
//                ]);
//
//                /** @var $order Order */
//                $order = $this->serviceOrder->createFromAgreement($dto, $user);
//            }

            event(new AcceptAgreementEvent($model));

            return $model;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
