<?php

use App\Foundations\Modules\Localization\Models\Language;
use App\Foundations\Modules\Localization\Repositories\LanguageRepository;
use App\Models\Settings\Settings;
use Carbon\Carbon;

if (!function_exists('logger_info')) {

    function logger_info($message, array $context = [], bool $important = true)
    {
        if(config('logging.channels.eyes.enable') || $important){
            Illuminate\Support\Facades\Log::channel('eyes')->info($message, $context);
        }
    }
}

if (!function_exists('logger_sync')) {

    function logger_sync($message, array $context = [], bool $important = true)
    {
        if(config('logging.channels.sync.enable') || $important){
            Illuminate\Support\Facades\Log::channel('sync')->info($message, $context);
        }
    }
}

if (!function_exists('json_to_array')) {
    function json_to_array(?string $jsonString = ''): array
    {
        return json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR) ?: [];
    }
}

if (!function_exists('array_to_json')) {
    function array_to_json(array $array, $options = 0): string
    {
        return json_encode($array, JSON_THROW_ON_ERROR | $options);
    }
}

if (!function_exists('std_to_array')) {
    function std_to_array(object $std): array
    {
        return json_decode(json_encode($std), true);
    }
}

if (!function_exists('make_transaction')) {
    /**
     * @param  Closure  $action
     * @param  array<Illuminate\Database\Connection>  $connections
     * @return mixed
     * @throws Throwable
     */
    function make_transaction(Closure $action, array $connections = []): mixed
    {
        return app(\App\Foundations\Services\Database\TransactionService::class)->handle($action, $connections);
    }
}

if (!function_exists('hash_data')) {

    function hash_data(array|string|int $data): string
    {
        if(is_array($data)){
            $data = json_encode($data);
        }

        return md5($data);
    }
}

if (!function_exists('cache_key')) {

    function cache_key(string $key, ...$data): string
    {
        return $key .'_'. hash_data($data);
    }
}

if (!function_exists('phone_clear')) {

    function phone_clear(string $phone): string
    {
        return str_replace(['+', '-', ' ', '.', '(', ')'], '', $phone);
    }
}

if (!function_exists('default_lang')) {

    function default_lang(): Language
    {
        $model = resolve(LanguageRepository::class)->getDefault();

        if(!$model){
            throw new \Exception(
                __('exceptions.localization.default_language_not_set')
            );
        }

        return $model;
    }
}

if (!function_exists('escape_like')) {
    /**
     * @param $string
     * @return mixed
     */
    function escape_like($string)
    {
        $search = ['%', '_'];
        $replace = ['\%', '\_'];
        return str_replace($search, $replace, $string);
    }
}

if (!function_exists('remove_underscore')) {

    function remove_underscore(string $str): string
    {
        return str_replace('_', ' ', $str);
    }
}

if (!function_exists('auth_user')) {
    function auth_user(): \Illuminate\Contracts\Auth\Authenticatable|\App\Models\Users\User|null
    {
        return Auth::guard(\App\Foundations\Modules\Permission\Models\Role::GUARD_USER)->user();
    }
}

if (!function_exists('byte_to_kb')) {
    function byte_to_kb(int $size)
    {
        return round($size / 1024);
    }
}

if (!function_exists('media_hash_file')) {
    function media_hash_file(string $filename, $extension): string
    {
        return md5($filename . microtime()) . '.' . $extension;
    }
}

if (!function_exists('to_bool')) {

    function to_bool($value): ?bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN,  FILTER_NULL_ON_FAILURE);
    }
}

if (!function_exists('to_bs_timezone')) {
    function to_bs_timezone(Carbon $date, string $timezone = null): Carbon
    {
        if (!$timezone) {
            $timezone = Settings::getParam('timezone') ?? 'UTC';
        }

        return $date->setTimezone($timezone);
    }
}

if (!function_exists('from_bs_timezone')) {
    function from_bs_timezone(string $format, string $date, string $timezone = null): Carbon
    {
        if (!$timezone) {
            $timezone = Settings::getParam('timezone') ?? 'UTC';
        }

        return Carbon::createFromFormat($format, $date, $timezone)->setTimezone('UTC');
    }
}

if (!function_exists('is_image')) {
    function is_image(string $extension): bool
    {
        return in_array($extension, config('filebrowser.mimetypes.images'));
    }
}

if (!function_exists('remove_trailing_slashes')) {
    function remove_trailing_slashes(?string $path = ''): string
    {
        $path = ltrim($path, '/');
        return rtrim($path, '/');
    }
}

if (!function_exists('is_testing')) {
    function is_testing(): bool
    {
        return config('app.env') === 'testing';
    }
}

if (!function_exists('is_in_range')) {
    // входит ли число (number) в диапазон двух чисел ($min, $max)
    function is_in_range(int|string $number, int $min, int $max): bool
    {
        if(!is_numeric($number)) return false;

        return $min <= $number && $number <= $max;
    }
}

if (!function_exists('percentage')) {
    // получения процента от суммы
    function percentage(float $total, float $percent): float
    {
        return ($percent / 100) * $total;
    }
}

if (!function_exists('price_with_discount')) {
    // получения процента от суммы
    function price_with_discount(float|int|null $price, float|int|null $discount): float
    {
        return round($price - (($price * $discount)/100), 2) ;
    }
}
