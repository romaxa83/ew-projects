<?php

namespace App\Traits;

trait SimpleHasher
{
    public static function hash(array $data): string
    {
        return md5(json_encode($data));
    }
}
