<?php

use App\Services\Database\TransactionService;
use Illuminate\Database\Connection;

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

if (!function_exists('deleteBackspace')) {

    function deleteBackspace($str)
    {
        $text = htmlentities($str);
        return str_replace("&nbsp;",'',$text);
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

if (!function_exists('normalizeSimpleData')) {

    function normalizeSimpleData(array $data): array
    {
        $temp = [];
        foreach($data as $k => $i){
            $temp[$k]['key'] = $k;
            $temp[$k]['name'] = $i;
        }

        return $temp;
    }
}

if (!function_exists('prettyPrice')) {

    function prettyPrice($price)
    {
        if($price){

            return number_format($price, 2, '.', '');
        }

        return $price;
    }
}

if (!function_exists('timestampToFront')) {

    function timestampToFront($timestamp)
    {
        return $timestamp * 100;
    }
}

if (!function_exists('mergeOneArray')) {
// обьединяет многомерный массив в один
    function mergeOneArray($arr)
    {
        $result = [];
        array_walk_recursive($arr, function ($item, $key) use (&$result) {
            $result[] = $item;
        });

        return $result;
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

