<?php

namespace WezomCms\Translates\UseCase;

class TranslateHash
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
