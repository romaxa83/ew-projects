<?php


namespace App\Contracts\Alerts;


interface AlertEnum
{

    public static function getFrontList(): array;

    public static function getBackList(): array;
}
