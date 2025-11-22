<?php

namespace App\GraphQL\Mutations\Order;

use App\DTO\Media\FileDTO;
use App\Exceptions\ErrorsCode;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Media\File;
use App\Models\Order\Order;
use App\Models\User\User;
use App\Repositories\Order\OrderRepository;
use App\Services\Media\UploadService;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class OrderAttachFile extends BaseGraphQL
{
    public function __construct(
        protected OrderService $service,
        protected OrderRepository $repository,
        private UploadService $uploadService,
    )
    {}

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
        /** @var $user User */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $args['model'] = File::MODEL_ORDER;
            $args['modelId'] = $args['id'];

            /** @var $order Order */
            $order = $this->repository->findByID($args['modelId']);

            if(null !== $order->getFileByType($args['type'])){
                throw new \DomainException(__('error.order.order have file', ['type' => $args['type']]), ErrorsCode::BAD_REQUEST);
            }

            $dto = FileDTO::byArgs($args);
            $this->uploadService->uploadFile($dto);

            $order->refresh();

            TelegramDev::info("Админ привязал файл ({$args['type']}) к заявке ({$order->id})", $user->name);

            return $order->getFileByType($args['type']);
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
