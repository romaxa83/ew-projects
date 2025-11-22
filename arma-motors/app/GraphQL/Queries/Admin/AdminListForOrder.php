<?php

namespace App\GraphQL\Queries\Admin;

use App\DTO\Admin\AdminDTO;
use App\Events\Admin\GeneratePassword;
use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\Order\Order;
use App\Repositories\Admin\AdminRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\Admin\AdminService;
use App\Services\Telegram\TelegramDev;
use App\Traits\GraphqlResponse;
use GraphQL\Error\Error;
use Illuminate\Database\Eloquent\Collection;

class AdminListForOrder extends BaseGraphQL
{
    use GraphqlResponse;

    public function __construct(
        protected AdminRepository $adminRepository,
        protected OrderRepository $orderRepository
    ){}

    /**
     * Return information about current user
     *
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return Collection
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): Collection
    {
        /** @var $sadmin Admin */
        $sadmin = \Auth::guard(Admin::GUARD)->user();
        try {
            /** @var $order Order */
            $order = $this->orderRepository->findOneBy('id', $args['orderId'], ['additions', 'service']);
            $dealershipId = $order->additions->dealership_id ?? null;

            $admins = $this->adminRepository->getListForOrder(
                $dealershipId,
                $order->service->getOrderDepartment(true)
            );

            return $admins;
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $sadmin->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

