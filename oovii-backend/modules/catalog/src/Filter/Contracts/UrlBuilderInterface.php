<?php

namespace WezomCms\Catalog\Filter\Contracts;

interface UrlBuilderInterface
{
    /**
     * @return UrlBuilderInterface
     */
    public function start(): UrlBuilderInterface;

    /**
     * @param  array|null  $keys
     * @return iterable
     */
    public function getParameters(array $keys = null): iterable;

    /**
     * @param $key
     * @param $value
     * @return UrlBuilderInterface
     */
    public function add($key, $value): UrlBuilderInterface;

    /**
     * @param $key
     * @return bool
     */
    public function has($key): bool;

    /**
     * @param $key
     * @param $value
     * @return bool
     */
    public function present($key, $value): bool;

    /**
     * @param  iterable  $parameters
     * @return string
     */
    public function generateUrl(iterable $parameters = []): string;

    /**
     * @param $key
     * @param $value
     * @return string
     */
    public function autoBuild($key, $value): string;

    /**
     * @param $key
     * @param $value
     * @return string
     */
    public function buildUrlWith($key, $value): string;

    /**
     * @param  string|array  $key
     * @param  mixed|null  $value
     * @return string
     */
    public function buildUrlWithout($key, $value = null): string;

    /**
     * @param $key
     * @param  null  $default
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Extract first value.
     *
     * @param $key
     * @param  null  $default
     * @return mixed
     */
    public function first($key, $default = null);

    /**
     * @param $key
     * @return bool
     */
    public function remove($key): bool;
}
