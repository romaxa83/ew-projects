<?php

namespace WezomCms\Core\Foundation;

use Illuminate\Support\Facades\Request;

class Helpers
{
    /**
     * @return string
     */
    public static function currentController()
    {
        $currentRouteName = Request::route()->getName();
        $controller = str_replace('admin.', '', $currentRouteName);
        $controller = explode('.', $controller);

        return array_get($controller, 0);
    }

    /**
     * @return string
     */
    public static function getBaseRouteName()
    {
        $currentRouteName = Request::route()->getName();
        $currentRouteName = explode('.', $currentRouteName);
        array_pop($currentRouteName);

        return implode('.', $currentRouteName);
    }

    /**
     * @param  string  $name
     * @return mixed
     */
    public static function convertFieldToDot(string $name)
    {
        return str_replace(['[', ']'], ['.', ''], $name);
    }

    /**
     * @param  iterable  $items
     * @param  string  $parentKey
     * @return array
     */
    public static function groupByParentId(iterable $items, string $parentKey = 'parent_id'): array
    {
        $result = [];
        foreach ($items as $item) {
            if (is_object($item)) {
                $result[$item->$parentKey][] = $item;
            } elseif (is_array($item)) {
                $result[$item[$parentKey]][] = $item;
            }
        }

        return $result;
    }

    /**
     * @param  iterable  $list
     * @param $id
     * @param  array  $result
     * @param  string  $parentKey
     * @return array
     */
    public static function getAllChildes(iterable $list, $id, &$result = [], $parentKey = 'parent_id')
    {
        if (!$id) {
            return $result;
        }
        foreach ($list as $obj) {
            if (is_array($obj)) {
                if ($obj[$parentKey] == $id) {
                    $result[] = $obj['id'];
                    static::getAllChildes($list, $obj['id'], $result, $parentKey);
                }
            } else {
                if ($obj->{$parentKey} == $id) {
                    $result[] = $obj->id;
                    static::getAllChildes($list, $obj->id, $result, $parentKey);
                }
            }
        }

        return $result;
    }

    /**
     * @param $url
     * @param  string  $size
     * @return string|null
     */
    public static function getYoutubePoster($url, $size = 'hqdefault'): ?string
    {
        $code = Helpers::getYoutubeId($url);
        if ($code) {
            return "https://img.youtube.com/vi/{$code}/{$size}.jpg";
        }

        return null;
    }

    /**
     * @param $url
     * @return mixed
     */
    public static function getYoutubeId($url)
    {
        preg_match('/(http(s|):|)\/\/(www\.|)yout(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $matches);

        return array_get($matches, 6);
    }

    /**
     * @param  string  $provider
     * @return bool
     */
    public static function providerLoaded(string $provider): bool
    {
        return app()->providerIsLoaded(trim($provider, '\\'));
    }

    /**
     * @param $bytes
     * @return string
     */
    public static function bytesToHuman($bytes): string
    {
        $units = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * @param $value
     * @return string
     */
    public static function countToHuman($value)
    {
        if ($value >= 1000000) {
            return number_format($value / 1000000, 1, '.', ' ') . 'M';
        } elseif ($value >= 1000) {
            return number_format($value / 1000, 1, '.', ' ') . 'k';
        }

        return number_format($value, 0, '.', ' ');
    }
}
