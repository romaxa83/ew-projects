<?php

namespace App\GraphQL\Mutations\Translation;

use App\GraphQL\BaseGraphQL;
use App\Services\Localizations\TranslationService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class TranslationsDelete extends BaseGraphQL
{
    public function __construct(private TranslationService $translationService)
    {}

    /**
     * @param  null  $_
     * @param  array<string, mixed>  $args
     *
     * @throws Error
     *
     * @return array
     */
    public function __invoke($_, array $args): array
    {
        try {
            $this->translationService->removeByPlaceAndOrKey($args['place'], $args['key'] ?? null);

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

