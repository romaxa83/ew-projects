<?php

namespace App\GraphQL\Queries\Localization;

use App\GraphQL\BaseGraphQL;
use App\Services\Localizations\TranslationService;
use App\Services\Telegram\TelegramDev;

class TranslationsHash extends BaseGraphQL
{
    public function __construct(private TranslationService $translationService)
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
        try {
            return [
                'status' => true,
                'hash' => $this->translationService->getHashByPlace($args['place']),
                'message' => ''
            ];
        } catch (\Throwable $e){
            TelegramDev::error(__FILE__, $e, null,TelegramDev::LEVEL_IMPORTANT);
            $this->throwExceptionError($e);
        }
    }
}

