<?php

namespace App\GraphQL\Queries;

use App\GraphQL\BaseGraphQL;
use App\Models\Hash;
use App\Repositories\HashRepository;
use App\Services\Telegram\TelegramDev;

class HashData extends BaseGraphQL
{
    public function __construct(protected HashRepository $repository)
    {}

    /**
     * @param null                 $_
     * @param array<string, mixed> $args
     *
     * @return array
     * @throws \GraphQL\Error\Error
     */
    public function __invoke($_, array $args): array
    {
//        dd($args);
        try {
            return [
                'status' => true,
                'hash' => (new Hash())->getHash($args['alias']),
                'message' => ''
            ];
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}
