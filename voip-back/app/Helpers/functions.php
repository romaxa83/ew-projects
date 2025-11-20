<?php

use App\Models\BaseModel;
use App\Models\Localization\Language;
use App\Models\Localization\Locale;
use Carbon\CarbonImmutable;
use Core\Services\Cache\LockerService;
use Core\Services\Database\TransactionService;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;
use JetBrains\PhpStorm\Pure;

if (!function_exists('logger_info')) {

    function logger_info($message, array $context = [], bool $important = true)
    {
        if(config('logging.channels.eyes.enable') || $important){
            Illuminate\Support\Facades\Log::channel('eyes')->info($message, $context);
        }
    }
}

if (!function_exists('logger_api')) {

    function logger_api($message, array $context = [], bool $important = true)
    {
        if(config('logging.channels.api.enable') || $important){
            Illuminate\Support\Facades\Log::channel('api')->info($message, $context);
        }
    }
}

if (!function_exists('logger_seq')) {

    function logger_seq($message, array $context = [], bool $important = true)
    {
        Illuminate\Support\Facades\Log::channel('sequence')->info($message, $context);
    }
}

if (!function_exists('convertMillisecondToSecond')) {

    function convertMillisecondToSecond($value): int
    {
        return (int)round($value / 1000, 2);
    }
}

if (!function_exists('jsonToArray')) {
    function jsonToArray(?string $jsonString = ''): array
    {
        return json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR) ?: [];
    }
}

if (!function_exists('timezone')) {
    function timezone(): string
    {
        return getallheaders()['Timezone'] ?? 'Europe/Kiev';
    }
}

if (!function_exists('stdToArray')) {
    function stdToArray(object $std): array
    {
        return json_decode(json_encode($std), true);
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
    /**
     * @return Collection<Locale>
     */
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

if (!function_exists('dateIntervalToSeconds')) {
    function dateIntervalToSeconds(DateInterval $interval): int
    {
        return $interval->d * 86400 + $interval->h * 3600
            + $interval->i * 60 + $interval->s + round($interval->f);
    }
}

if (!function_exists('prettyStr')) {

    function prettyStr(string $str): string
    {
        return str_replace('_', ' ', $str);
    }
}

if (!function_exists('secondToTime')) {

    function secondToTime(int $second): string
    {
//        return gmdate("Y-m-d H:i:s", $second);
        $d = gmdate("Y-m-d H:i:s", $second);
        $date = \Carbon\CarbonImmutable::createFromFormat("Y-m-d H:i:s", $d);

        $hour = $date->hour;
        $min = $date->minute;
        $sec = $date->second;

        if($date->month > 1){
            $days = 0;
            for ($i = ($date->month - 1); $i >= 1; --$i) {
                $days += cal_days_in_month(CAL_GREGORIAN,$i, \Carbon\CarbonImmutable::now()->year);
            }
            $hour += $days * 24;
        }
        if($date->day > 1){
            $hour += ($date->day - 1) * 24;
        }

        if(strlen($hour) == 1){
            $hour = '0'. (string)$hour;
        }
        if(strlen($min) == 1){
            $min = '0'. (string)$min;
        }
        if(strlen($sec) == 1){
            $sec = '0'. (string)$sec;
        }

        return "$hour:$min:$sec";
    }
}

if (!function_exists('valueForExcelRow')) {

    function valueForExcelRow(null|string|int $value): string
    {
        if(
            $value === null
            || $value === '0'
            || $value === 0
            || $value === '00:00:00'
            || $value === ''
            || $value === ' '
            || $value === \App\Models\Reports\Item::UNKNOWN
        ){
            $value = '-';
        }

        return (string)$value;
    }
}

if (!function_exists('dateByTz')) {

    function dateByTz(\Carbon\CarbonImmutable|\Carbon\Carbon $value): \Carbon\CarbonImmutable|\Carbon\Carbon
    {
        if(substr(config('app.current_offset_to_utc'), 0, 1) == '-'){
            $date = $value->subHours(substr(config('app.current_offset_to_utc'), 1));
        } else {
            $date = $value->addHours(config('app.current_offset_to_utc'));
        }

        return $date;
    }
}

if (!function_exists('remove_underscore')) {

    function remove_underscore(string $str): string
    {
        return str_replace('_', ' ', $str);
    }
}

if (!function_exists('pretty_file_name')) {

    function pretty_file_name(string $str): string
    {
        return str_replace([' ', ',' , '-', ':', ';'], '_', $str);
    }
}

if (!function_exists('current_day')) {

    function current_day(null|\Carbon\Carbon|\Carbon\CarbonImmutable $now = null): string
    {
        if(!$now){
            $now = dateByTz(CarbonImmutable::now());
        }

        return strtolower($now->isoFormat('dddd'));
    }
}
