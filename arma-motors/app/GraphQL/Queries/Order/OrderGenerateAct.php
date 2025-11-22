<?php

namespace App\GraphQL\Queries\Order;

use App\GraphQL\BaseGraphQL;
use App\Models\Order\Order;
use App\Repositories\Order\OrderRepository;
use App\Services\AA\Commands\GetAct;
use App\Services\AA\RequestService;
use App\Services\Media\File\FileService;
use App\Services\Telegram\TelegramDev;

class OrderGenerateAct extends BaseGraphQL
{
    public function __construct(
        protected OrderRepository $repo,
        protected RequestService $requestService,
        protected FileService $fileService
    )
    {}

    /**
     * uuid заявки - по которой можно сгенерить
     * ba8b6832-5742-11ec-8277-4cd98fc26f14
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return array
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
        try {
            $order = $this->repo->findByID($args['id']);

            if(!$order->uuid){
                throw new \Exception("Order [{$args['id']}] has not uuid");
            }

//            $res = $this->requestService->getDataAct($order->uuid);
//            $data = $res->data;

            // test data
            $data = GetAct::testData();

            $this->fileService->generateOrderPDF($order, $data, Order::FILE_ACT_TYPE);

            return $this->successResponse(__("message.order.generate act"));
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}



