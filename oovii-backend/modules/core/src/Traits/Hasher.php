<?php

namespace WezomCms\Core\Traits;

trait Hasher
{
    public function hash(array $data): string
    {
        return md5(json_encode($data));
    }
}
