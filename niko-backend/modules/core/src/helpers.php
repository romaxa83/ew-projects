<?php

use WezomCms\Core\Contracts\SettingsInterface;
use WezomCms\Core\Image\ImageService;

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
        return \LaravelLocalization::getLocalizedURL(
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
