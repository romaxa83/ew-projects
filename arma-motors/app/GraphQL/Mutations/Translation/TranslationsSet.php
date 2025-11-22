<?php

namespace App\GraphQL\Mutations\Translation;

use App\GraphQL\BaseGraphQL;
use App\Services\Localizations\TranslationService;
use App\Services\Telegram\TelegramDev;
use GraphQL\Error\Error;

class TranslationsSet extends BaseGraphQL
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
            $this->translationService->createOrUpdate($args);

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



