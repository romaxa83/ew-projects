<?php

namespace App\GraphQL\Queries\Order;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\Order\OrderRepository;
use App\Services\Telegram\TelegramDev;
use App\Types\Order\Status;
use App\Types\Permissions;

class OrderCountNew extends BaseGraphQL
{
    public function __construct(protected OrderRepository $repository)
    {}

    /**
     *
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return array
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
        /** @var $admin Admin */
        $admin = \Auth::guard(Admin::GUARD)->user();
        try {
            $payload = [];
            if(!($admin->isSuperAdmin() || $admin->hasPermissionTo(Permissions::ORDER_CAN_SEE))){
                if($admin->service_id == null){
                    return [
                        "key" => "count",
                        "name" => 0,
                    ];
                }
                $payload = ['service_id' => $admin->service_id];
            }

            $status = Status::create(Status::DRAFT);

            return [
                "key" => "count",
                "name" => $this->repository->countByStatus($status, $payload),
            ];
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

