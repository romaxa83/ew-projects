<?php

namespace App\Foundations\Modules\Utils\Tokenizer;

use App\Foundations\Models\BaseAuthenticatableModel;
use App\Foundations\Modules\Utils\Tokenizer\Entities\TokenEntity;
use App\Foundations\Modules\Utils\Tokenizer\Exceptions\TokenAssertExceptions;
use App\Foundations\Modules\Utils\Tokenizer\Exceptions\TokenDecryptException;
use App\Foundations\Modules\Utils\Tokenizer\Exceptions\TokenEncryptException;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Crypt;

class Tokenizer
{
    /**
     * @throws TokenDecryptException
     */
    public static function decryptToken(string $token): TokenEntity
    {
        try {
            return new TokenEntity(
                json_decode(
                    Crypt::decryptString($token),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                )
            );
        } catch (\Exception $e) {
            throw new TokenDecryptException($e->getMessage());
        }
    }

    /**
     * @throws TokenEncryptException
     */
    public static function encryptToken(array $payload): string
    {
        try {
            return Crypt::encryptString(
                json_encode(
                    [
                        'model_id' => $payload['model_id'],
                        'model_class' => $payload['model_class'],
                        'time_at' => CarbonImmutable::now()->timestamp,
                        'field_check_code' => $payload['field_check_code'],
                        'code' => $payload['code'],
                    ],
                    JSON_THROW_ON_ERROR
                )
            );
        } catch (\Exception $e) {
            throw new TokenEncryptException($e->getMessage());
        }
    }

    public static function assertToken(TokenEntity $entity, BaseAuthenticatableModel $model)
    {
        if($model->getAttribute($entity->fieldCheckCode) != $entity->code){
            throw new TokenAssertExceptions(__('exceptions.token.not_verified'));
        }
    }

    public static function checkToken(string $token, int $live): bool
    {
        $tokenEntity = self::decryptToken($token);

        $time = CarbonImmutable::createFromTimestamp($tokenEntity->timeAt)
            ->addMinutes($live);

        if($time < CarbonImmutable::now()) return false;

        $model = self::getModel($tokenEntity);

        if(!$model) return false;

        return $model->getAttribute($tokenEntity->fieldCheckCode) == $tokenEntity->code;
    }

    public static function getModel(TokenEntity $token)
    {
        return $token->modelClass::find($token->modelId);
    }
}
