<?php

namespace App\GraphQL\Queries\Admin;

use App\GraphQL\BaseGraphQL;
use App\Helpers\Month;
use App\Models\Admin\Admin;
use App\Models\Catalogs\Car\Brand;
use App\Repositories\Admin\AdminRepository;
use App\Repositories\Catalog\Car\BrandRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\User\CarOrder\OrderCarRepository;
use App\Repositories\User\UserRepository;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use App\Traits\GraphqlResponse;

class DashboardCounts extends BaseGraphQL
{
    use GraphqlResponse;

    public function __construct(
        protected UserRepository $userRepository,
        protected AdminRepository $adminRepository,
        protected OrderRepository $orderRepository,
        protected OrderCarRepository $orderCarRepository,
    ){}
    /**
     * Return information about current user
     *
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return Admin
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
        /** @var $admin Admin */
        $admin = \Auth::guard(Admin::GUARD)->user();
        try {
            return [
                'usersCount' => $this->userRepository->getCountForDashboard(),
                'employeeCount' => $this->adminRepository->getCountForDashboard(),
                'orderCount' => $this->orderRepository->getCountForDashboard(),
                'orderCarCount' => $this->orderCarRepository->getCountForDashboard()
            ];
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $admin->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}


