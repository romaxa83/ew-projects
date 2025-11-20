<?php

use App\Services\Database\TransactionService;
use Carbon\Carbon;
use Illuminate\Database\Connection;

if (!function_exists('prettyAppName')) {

    function prettyAppName()
    {
        return str_replace('_', ' ', config('app.name'));
    }
}
if (!function_exists('normalizeFloat')) {

    function normalizeFloat($data)
    {
        return str_replace(',', '.', $data);
    }
}
if (!function_exists('cutPercent')) {

    function cutPercent($data)
    {
        return str_replace('%', '', $data);
    }
}
if (!function_exists('normalizeNumeric')) {

    function normalizeNumeric($data)
    {
        return cutPercent(normalizeFloat(trim($data)));
    }
}
if (!function_exists('parseParamsByComa')) {

    function parseParamsByComa(string $str): array
    {
        return explode(',', $str);
    }
}
if (!function_exists('parseDateForArray')) {

    function parseDateForArray(string $str): array
    {
        $temp = explode(',', $str);
        $new = array_map(function($item){
            return trim($item);
        }, $temp);

        return $new;
    }
}

if (!function_exists('array_undot')) {

    function array_undot($array): array
    {
        $results = [];

        foreach ($array as $key => $value) {
            \Illuminate\Support\Arr::set($results, $key, $value);
        }

        return $results;
    }
}

if (!function_exists('parse_date_query')) {

    function parse_date_query($value): array
    {
        if(is_array($value) && count($value) == 2){
            $from = strstr(current($value), ':') ? current($value) : Carbon::parse(current($value))->format('Y-m-d') . ' 00:00:00';
            $to = strstr(last($value), ':') ? last($value) : Carbon::parse(last($value))->format('Y-m-d') . ' 23:59:59';

            return [$from, $to];
        }

        return [
            Carbon::parse($value)->format('Y-m-d') . ' 00:00:00',
            Carbon::parse($value)->format('Y-m-d') . ' 23:59:59'
        ];
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

