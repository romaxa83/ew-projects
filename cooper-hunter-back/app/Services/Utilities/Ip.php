<?php

namespace App\Services\Utilities;

class Ip
{
    public function real(): string
    {
        /*
         * This is bad as HTTP_CLIENT_IP and HTTP_X_FORWARDED_FOR can be forged. Only REMOTE_ADDR cannot.
         */

        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $_SERVER['REMOTE_ADDR'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
            $_SERVER['HTTP_CLIENT_IP'] = $_SERVER["HTTP_CF_CONNECTING_IP"];
        }

        $client = $_SERVER['HTTP_CLIENT_IP'] ?? null;
        $forward = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
        $remote = $_SERVER['REMOTE_ADDR'];

        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }

        return $ip;
    }
}