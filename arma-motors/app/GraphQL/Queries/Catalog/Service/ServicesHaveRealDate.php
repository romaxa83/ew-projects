<?php

namespace App\GraphQL\Queries\Catalog\Service;

use App\GraphQL\BaseGraphQL;
use App\Repositories\Catalog\Service\ServiceRepository;
use App\Services\Telegram\TelegramDev;
use Illuminate\Database\Eloquent\Collection;

class ServicesHaveRealDate extends BaseGraphQL
{
    public function __construct(
        protected ServiceRepository $repository,
    ){}

    /**
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return Collection
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): Collection
    {
        try {
            return $this->repository->getHaveRealTime();
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

