<?php

use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Illuminate\Http\Response;
use WezomCms\Core\Contracts\SettingsInterface;
use WezomCms\Core\Image\ImageService;
use WezomCms\Core\Services\PhoneMaskService;

if (!function_exists('settings')) {
    /**
     * @param  null|string  $key
     * @param  mixed  $default
     * @return mixed|SettingsInterface
     */
    function settings($key = null, $default = null)
    {
        /** @var SettingsInterface $settings */
        $settings = app(SettingsInterface::class);

        if (is_null($key)) {
            return $settings;
        }

        return $settings->get($key, $default);
    }
}

if (!function_exists('glob_recursive')) {
    /**
     * Find path names matching a pattern recursively
     *
     * @param $pattern
     * @param  int  $flags
     * @return array
     */
    function glob_recursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, glob_recursive($dir . '/' . basename($pattern), $flags));
        }

        return $files;
    }
}

if (!function_exists('route_localized')) {
    /**
     * Generate the URL to a named route based on current locale
     *
     * @param  array|string  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @param  string|null  $locale
     * @return string
     */
    function route_localized($name, $parameters = [], $absolute = true, $locale = null)
    {
        return LaravelLocalization::getLocalizedURL(
            $locale ? : app()->getLocale(),
            route($name, $parameters, $absolute)
        );
    }
}


if (!function_exists('image_url')) {
    /**
     * Generate the URL to image with .webp if browser support & file exists
     *
     * @param  string  $path
     * @param  mixed  $parameters
     * @param  bool|null  $secure
     * @return string
     */
    function image_url($path = null, $parameters = [], $secure = null)
    {
        if (ImageService::webPSupport() && is_file(public_path($path . '.webp'))) {
            $path .= '.webp';
        }

        return url($path, $parameters, $secure);
    }
}

if (!function_exists('published_scope')) {
    /**
     * Apply published scope.
     *
     * @return callable
     */
    function published_scope()
    {
        return function ($query) {
            $query->published();
        };
    }
}

if (!function_exists('money')) {
    /**
     * @param  null  $amount
     * @param  bool  $currency
     * @return \WezomCms\Core\Foundation\Money|string
     */
    function money($amount = null, $currency = false)
    {
        $money = app('money');

        if (!$amount && func_num_args() === 0) {
            return $money;
        }

        $result = $money->format($amount);

        if ($currency) {
            return $money->addCurrency($result);
        }

        return $result;
    }
}

if (!function_exists('remove_phone_mask')) {
    /**
     * @param  string|null  $phone
     * @param  string|null  $format
     * @return string|null
     */
    function remove_phone_mask(?string $phone, ?string $format = null): ?string
    {
        return (new PhoneMaskService($format))->removePhoneMask($phone);
    }
}

if (!function_exists('apply_phone_mask')) {
    /**
     * @param  string|null  $phone
     * @param  string|null  $format
     * @return string
     */
    function apply_phone_mask(?string $phone, ?string $format = null): string
    {
        return (new PhoneMaskService($format))->applyMask($phone);
    }
}

if (!function_exists('json_to_array')) {
    function json_to_array(?string $jsonString = ''): array
    {
        return json_decode($jsonString, true) ?: [];
    }
}

if (!function_exists('array_to_json')) {
    function array_to_json(array $array, $options = 0): string
    {
        return json_encode($array, $options);
    }
}

if (!function_exists('prettyAppName')) {

    function prettyAppName()
    {
        return str_replace('_', ' ', env('APP_NAME'));
    }
}

if (!function_exists('camel_to_snake')) {

    function camel_to_snake($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
}

if (!function_exists('snakeToCamel')) {

    function snakeToCamel($input)
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $input))));
    }
}

if (!function_exists('arrayKeyToCamel')) {

    function arrayKeyToCamel(array $array): array
    {
        $tmp = [];
        foreach ($array as $key => $item){
            $tmp[snakeToCamel($key)] = $item;
        }

        return $tmp;
    }
}

if (!function_exists('isApiRequest')) {

    function isApiRequest(\Illuminate\Http\Request $request): bool
    {
        $url = $request->url();
        $p = explode('/', $url);

        return in_array('api', $p);
    }
}

if (!function_exists('prettyPhone')) {

    function prettyPhone($value): string
    {
        return str_replace(['(', ')', '-', ' '], '', $value);
    }
}

if (!function_exists('errorCode')) {
    function errorCode(Throwable $e): int
    {
        if ($e instanceof HttpExceptionInterface) {
            return $e->getStatusCode();
        }

        $class = get_class($e);

        return match($class) {
            AuthorizationException::class => Response::HTTP_FORBIDDEN,
            default => $e->getCode(),
        };
    }
}

if (!function_exists('isEnv')) {
    /**
     * Checks application environment
     *
     * @param string $environment
     *
     * @return bool
     */
    function isEnv(string $environment): bool
    {
        return env('APP_ENV') === $environment;
    }
}

if (!function_exists('isLocal')) {
    /**
     * Checks if application environment is `local`
     *
     * @return bool
     */
    function isLocal(): bool
    {
        return isEnv('local');
    }
}
