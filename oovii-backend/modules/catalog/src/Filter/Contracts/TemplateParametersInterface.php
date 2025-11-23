<?php

namespace WezomCms\Catalog\Filter\Contracts;

interface TemplateParametersInterface
{
    /**
     * Return array of all supported parameters
     *
     * @return array
     */
    public static function availableParameters(): iterable;
}
