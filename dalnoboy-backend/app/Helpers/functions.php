<?php

use App\Helpers\Dto\RefreshTokenDto;
use App\Models\BaseModel;
use App\Models\Localization\Language;
use Core\Services\Cache\LockerService;
use Core\Services\Database\TransactionService;
use Defuse\Crypto\Crypto;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;
use Laravel\Passport\RefreshToken;

if (!function_exists('jsonToArray')) {
    function jsonToArray(?string $jsonString = ''): array
    {
        return json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR) ?: [];
    }
}

if (!function_exists('arrayToJson')) {
    function arrayToJson(array $array, $options = 0): string
    {
        return json_encode($array, JSON_THROW_ON_ERROR | $options);
    }
}

if (!function_exists('isTesting')) {
    #[Pure] function isTesting(): bool
    {
        return config('app.env') === 'testing';
    }
}

if (!function_exists('isProd')) {
    #[Pure] function isProd(): bool
    {
        return config('app.env') === 'production';
    }
}

if (!function_exists('languages')) {
    /**
     * @return Collection|Language[]
     */
    function languages(): Collection|array
    {
        return app('localization')->getAllLanguages();
    }
}

if (!function_exists('locales')) {
    function locales(): Collection
    {
        return app('locales')->getAllLocales();
    }
}

if (!function_exists('defaultLanguage')) {
    function defaultLanguage(): Language
    {
        return app('localization')->getDefault();
    }
}

if (!function_exists('yesOrNo')) {
    function yesOrNo(mixed $value): string
    {
        return (bool)$value ? 'yes' : 'no';
    }
}

if (!function_exists('trimDS')) {
    function trimDS(string $value): string
    {
        return trim($value, DIRECTORY_SEPARATOR);
    }
}

if (!function_exists('locker')) {
    function locker(): LockerService
    {
        return app(LockerService::class);
    }
}

if (!function_exists('toModelKey')) {
    function toModelKey(BaseModel|int|string $model): int|string
    {
        return $model instanceof BaseModel
            ? $model->getKey()
            : $model;
    }
}

if (!function_exists('makeTransaction')) {
    /**
     * @param  Closure  $action
     * @param  array<Connection>  $connections
     * @return mixed
     * @throws Throwable
     */
    function makeTransaction(Closure $action, array $connections = []): mixed
    {
        return app(TransactionService::class)->handle($action, $connections);
    }
}

if (!function_exists('getCurrentRunningTime')) {
    function getCurrentRunningTime(): float
    {
        return (microtime(true) - LARAVEL_START);
    }
}

if (!function_exists('builderToSql')) {
    /**
     * @param  \Illuminate\Database\Eloquent\Builder|Builder  $builder
     * @return string
     */
    function builderToSql($builder): string
    {
        $sql = str_replace('?', '%s', $builder->toSql());

        return vsprintf($sql, $builder->getBindings());
    }
}

if (!function_exists('getAccessTokenData')) {
    #[ArrayShape([
        "aud" => "string",
        "jti" => "string",
        "iat" => "float",
        "nbf" => "float",
        "exp" => "float",
        "sub" => "string",
        "scopes" => "array"
    ])]
    function getAccessTokenData(
        string $token
    ): ?array {
        $token = trim(preg_replace("/bearer/i", '', $token));

        $split = explode('.', $token);

        if (empty($split[1])) {
            return null;
        }

        return json_decode(
            base64_decode($split[1]),
            true
        );
    }
}

if (!function_exists('getRefreshTokenData')) {
    function getRefreshTokenData(
        string $token,
        bool $withDbData = false
    ): RefreshTokenDto {
        $key = substr(config('app.key'), 7);
        $key = base64_decode($key);

        $tokenData = Crypto::decryptWithPassword($token, $key);

        $tokenData = json_decode(
            $tokenData,
            true
        );
        if (!$withDbData) {
            return RefreshTokenDto::init($tokenData);
        }

        $refreshToken = RefreshToken::query()
            ->where('id', $tokenData['refresh_token_id'])
            ->first();

        return RefreshTokenDto::init(
            array_merge(
                $tokenData,
                $refreshToken->toArray()
            )
        );
    }
}

if (!function_exists('isBackOffice')) {
    function isBackOffice(): bool
    {
        return Str::contains(
            mb_convert_case(
                request()->getPathInfo(),
                MB_CASE_LOWER
            ),
            'backoffice'
        );
    }
}
