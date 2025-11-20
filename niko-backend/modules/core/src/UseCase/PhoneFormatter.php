<?php

namespace WezomCms\Core\UseCase;

class PhoneFormatter
{
    public static function onlyNumber(string $phone)
    {
        return (int)str_replace('-', '', str_replace('(', '', str_replace(')', '', str_replace(' ', '', str_replace('+','', $phone)))));;
    }
}
