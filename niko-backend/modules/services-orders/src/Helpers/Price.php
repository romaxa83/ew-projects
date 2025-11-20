<?php

namespace WezomCms\ServicesOrders\Helpers;

class Price
{
    public static function toDB($price)
    {
        if($price != 0){
            return $price * 100;
        }

        return $price;
    }

    public static function fromDB($price)
    {
        if($price != 0){
            return $price / 100;
        }

        return $price;
    }
}

