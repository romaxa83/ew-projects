<?php

namespace WezomCms\Core\Contracts;

use WezomCms\Core\Drivers\DetectSourceLocaleException;

interface TranslatorDriverInterface
{
    /**
     * @param  string  $source
     * @param  string  $to
     * @param  string|null  $from
     * @return string|null
     *
     * @throws DetectSourceLocaleException
     */
    public function translate(string $source, string $to, ?string $from = null): ?string;
}
