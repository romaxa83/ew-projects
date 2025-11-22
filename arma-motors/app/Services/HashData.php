<?php

namespace App\Services;

class HashData
{
    /**
     * @param array $data
     * @return string
     */
    public static function hash(array $data): string
    {
        return md5(json_encode($data));
    }
}
