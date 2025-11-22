<?php

namespace App\GraphQL\Queries\Admin;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Repositories\Catalog\Car\BrandRepository;
use App\Repositories\Order\OrderRepository;
use App\Services\Order\OrderService;
use App\Services\Telegram\TelegramDev;
use App\Traits\GraphqlResponse;

class DashboardCountOrders extends BaseGraphQL
{
    use GraphqlResponse;

    public function __construct(
        protected OrderRepository $repository,
        protected OrderService $service,
        protected BrandRepository $brandRepository,
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
            $mainBrand = $this->brandRepository->getMain();

            return $this->service->getDataForCountOrdersDashboard($args['year'], $mainBrand);
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, $admin->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

