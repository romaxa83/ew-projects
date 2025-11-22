<?php

namespace App\GraphQL\Queries;

use App\GraphQL\BaseGraphQL;
use App\Models\AA\AAOrder;
use App\Services\Telegram\TelegramDev;

class Communications extends BaseGraphQL
{
    public function __construct()
    {}

    /**
     * Return information about current user
     *
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return array
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
//        $m = AAOrder::query()->where('id', 4)->first();
//        dd($m->planning);
        try {
            return normalizeSimpleData(\App\Types\Communication::list());

        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

