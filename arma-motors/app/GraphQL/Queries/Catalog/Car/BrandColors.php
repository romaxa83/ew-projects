<?php

namespace App\GraphQL\Queries\Catalog\Car;

use App\GraphQL\BaseGraphQL;
use App\Models\Catalogs\Car\Brand;
use App\Services\Telegram\TelegramDev;

class BrandColors extends BaseGraphQL
{
    /**
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return array
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
        try {
            return normalizeSimpleData(Brand::colors());
        } catch (\Throwable $e) {
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

