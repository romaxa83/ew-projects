<?php

use App\Events\ModelChanged;
use App\Models\Admins\Admin;
use App\Models\Users\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use App\Models\BodyShop\Settings\Settings;

if (!function_exists('unique_random')) {
    /**
     * @param $table
     * @param $col
     * @param $string
     * @return string
     */
    function unique_random($table, $col, $string)
    {
        $unique = false;

        $tested = [];
        do {
            $string = str_replace(' ', '', $string);
            $string = '0123456789' . $string;
            $random = str_shuffle($string);
            $random = substr($random, -10);

            if (in_array($random, $tested)) {
                continue;
            }

            $count = DB::table($table)->where($col, '=', $random)->count();
            $validation = preg_match('/^[A-Za-z0-9]+$/', $random);
            $tested[] = $random;
            if ($count == 0 && $validation == 1) {
                $unique = true;
            }
        } while (!$unique);
        return $random;
    }
}

if (!function_exists('escapeLike')) {
    /**
     * @param $string
     * @return mixed
     */
    function escapeLike($string)
    {
        $search = ['%', '_'];
        $replace = ['\%', '\_'];
        return str_replace($search, $replace, $string);
    }
}

if (!function_exists('json_to_array')) {
    function json_to_array(?string $jsonString = ''): array
    {
        return json_decode($jsonString, true) ?: [];
    }
}
if (!function_exists('media_hash_file')) {
    function media_hash_file(string $filename, $extension): string
    {
        return md5($filename . microtime()) . '.' . $extension;
    }
}
if (!function_exists('byte_to_kb')) {
    function byte_to_kb(int $size)
    {
        return round($size / 1024);
    }
}
if (!function_exists('array_to_json')) {
    function array_to_json(array $array, $options = 0): string
    {
        return json_encode($array, $options);
    }
}

if (!function_exists('isEnv')) {
    /**
     * Checks application environment
     *
     * @param string $environment
     * @return string
     */
    function isEnv(string $environment)
    {
        return env('APP_ENV') === $environment;
    }
}

if (!function_exists('isLocal')) {
    /**
     * Checks if application environment is `local`
     *
     * @return string
     */
    function isLocal()
    {
        return isEnv('local');
    }
}

if (!function_exists('isProd')) {
    /**
     * Checks if application environment is `production`
     *
     * @return string
     */
    function isProd()
    {
        return isEnv('production');
    }
}
if (!function_exists('array_diff_assoc_recursive')) {
    /**
     * Check difference of two assoc arrays
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    function array_diff_assoc_recursive(array $array1, array $array2): array
    {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $newDifference = array_diff_assoc_recursive($value, $array2[$key]);
                    if (!empty($newDifference)) {
                        $difference[$key] = $newDifference;
                    }
                }
            } else {
                if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                    $difference[$key] = $value;
                }
            }
        }
        return $difference;
    }
}

if (!function_exists('array_diff_assoc_recursive_detail')) {
    /**
     * Check difference of two assoc arrays
     *
     * @param array $array1
     * @param array $array2
     * @return array
     */
    function array_diff_assoc_recursive_detail(array $array1, array $array2): array
    {
        $difference = [];
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key]) || !is_array($array2[$key])) {
                    $difference['new'][] = $value;
                } else {
                    $newDifference = array_diff_assoc_recursive_detail($value, $array2[$key]);
                    if (!empty($newDifference)) {
                        $difference[$key] = $newDifference;
                    }
                }
            } else {
                if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
                    $difference[$key] = ['new' => $value, 'old' => $array2[$key]];
                }
            }
        }
        return $difference;
    }
}

if (!function_exists('remove_trailing_slashes')) {
    function remove_trailing_slashes(?string $path = ''): string
    {
        $path = ltrim($path, '/');
        return rtrim($path, '/');
    }
}

if (!function_exists('isTesting')) {
    function isTesting(): bool
    {
        return config('app.env') === 'testing';
    }
}

if (!function_exists('isImage')) {
    function isImage(string $extension): bool
    {
        return in_array($extension, config('filebrowser.mimetypes.images'));
    }
}

if (!function_exists('phone_format')) {
    function phone_format(string $phone): ?string
    {
        if (Str::length($phone) !== 11) {
            if (Str::length($phone) === 10) {
                $phone = config('phones.prefixes.default') . $phone;
            } else {
                return null;
            }
        }

        return preg_replace('/^(?<prefix>\d)(?<code>\d{3})(?<first>\d{3})(?<second>\d{4})$/', "+$1 ($2) $3-$4", $phone);
    }
}

if (!function_exists('addBusinessDays')) {
    function addBusinessDays($startTimestamp, $businessDaysCount): Carbon
    {
        return Carbon::createFromTimestamp($startTimestamp)
            ->addBusinessDays($businessDaysCount);
    }
}

if (!function_exists('modelChanged')) {
    function modelChanged($model, $message, $attr = null): void
    {
        event(new ModelChanged($model, $message, $attr));
    }
}

if (!function_exists('guardUser')) {
    function guardUser()
    {
        return Auth::guard(User::GUARD);
    }
}

if (!function_exists('authUser')) {
    function authUser(): ?User
    {
        return guardUser()->user();
    }
}

if (!function_exists('guardAdmin')) {
    function guardAdmin()
    {
        return Auth::guard(Admin::GUARD);
    }
}

if (!function_exists('authAdmin')) {
    function authAdmin(): ?Admin
    {
        return guardAdmin()->user();
    }
}

if (!function_exists('toMoney')) {
    function toMoney($amount, string $currencySymbol = '$'): string
    {
        $amount = round((float)$amount,2);
        return $currencySymbol.number_format($amount, 2, '.', ' ');
    }
}

if (!function_exists('getGuard')) {
    function getGuard(): ?string
    {
        $guards = array_keys(config('auth.guards'));
        foreach ($guards as $guard) {
            if(Auth::guard($guard)->check()) {
                return $guard;
            }
        }
        return null;
    }
}

if (!function_exists('isAdminPanel')) {
    function isAdminPanel(): bool
    {
        return request()->header('Admin-Panel', 'false') === 'true';
    }
}

if (!function_exists('fromBSTimezone')) {
    function fromBSTimezone(string $format, string $date, string $timezone = null): Carbon
    {
        if (!$timezone) {
            $timezone = Settings::getParam('timezone') ?? 'UTC';
        }

        return Carbon::createFromFormat($format, $date, $timezone)->setTimezone('UTC');
    }
}

if (!function_exists('toBSTimezone')) {
    function toBSTimezone(Carbon $date, string $timezone = null): Carbon
    {
        if (!$timezone) {
            $timezone = Settings::getParam('timezone') ?? 'UTC';
        }

        return $date->setTimezone($timezone);
    }
}

if (!function_exists('parseIpAddresses')) {
    function parseIpAddresses(string $hosts): array
    {
        $result = [];
        if ($hosts) {
            foreach (explode(',', $hosts) as $ip) {
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    $result[] = $ip;
                }
            }
        }
        return $result;
    }
}


if (!function_exists('phone_clear')) {

    function phone_clear(string $phone): string
    {
        return str_replace(['+', '-', ' ', '.', '(', ')'], '', $phone);
    }
}

if (!function_exists('logger_info')) {

    function logger_info($message, array $context = [], bool $important = true)
    {
        if(config('logging.channels.eyes.enable')){
            Illuminate\Support\Facades\Log::channel('eyes')->info($message, $context);
        }
    }
}

if (!function_exists('logger_flespi')) {

    function logger_flespi($message, array $context = [], bool $important = true)
    {
        if(config('logging.channels.flespi.enable')){
            Illuminate\Support\Facades\Log::channel('flespi')->info($message, $context);
        }
    }
}

if (!function_exists('convert_speed_from_km_to_miles')) {

    function convert_speed_from_km_to_miles($value): float
    {
        return $value * 0.621371;
    }
}

if (!function_exists('convert_meters_to_miles')) {

    function convert_meters_to_miles(float $meters): float
    {
        return round($meters * 0.000621371, 2);
    }
}


if (!function_exists('remove_underscore')) {

    function remove_underscore(string $str): string
    {
        return str_replace('_', ' ', $str);
    }
}

if (!function_exists('convert_sec_to_hour_and_min')) {

    function convert_sec_to_hour_and_min(?int $seconds): ?string
    {
        if(!$seconds) return $seconds;

        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $minutes = $minutes - ($hours * 60);

        if(!$hours) return "$minutes m";

        return "$hours h $minutes m";
    }
}

if (!function_exists('to_bool')) {

    function to_bool($value): ?bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN,  FILTER_NULL_ON_FAILURE);
    }
}

if (!function_exists('bool_as_string')) {

    function bool_as_string(bool $value): string
    {
        return $value ? "true" : "false";
    }
}

if (!function_exists('simple_hash')) {

    function simple_hash($data): string
    {
        if(is_array($data)){
            $data = json_encode($data);
        }

        return md5($data);
    }
}

if (!function_exists('american_date_to_utc')) {

    function american_date_to_utc($date): CarbonImmutable
    {
//        $date = CarbonImmutable::make($date);

        $timeZone = Settings::getParam('timezone') ?? 'UTC';
        $offset = $date->setTimezone($timeZone)->offsetHours;

        if($offset < 0){
            $target = $date->addHours(abs($offset));
        } else {
            $target =  $date->subHours($offset);
        }

        return $target;
    }
}
