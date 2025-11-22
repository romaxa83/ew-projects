<?php

namespace App\GraphQL\Queries\User;

use App\GraphQL\BaseGraphQL;
use App\Models\Admin\Admin;
use App\Models\User\Car;
use App\Models\User\User;
use App\Repositories\User\CarRepository;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;
use Illuminate\Database\Eloquent\Collection;

class CarsOrder extends BaseGraphQL
{
    public function __construct(protected CarRepository $repository)
    {}

    /**
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return Collection
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): Collection
    {
        /** @var $user Admin */
        $user = \Auth::guard(Admin::GUARD)->user();
        try {
            $orders = $this->repository->getAllByOrderAndUser($args['userId']);

            return $orders;
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, $user->name,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
