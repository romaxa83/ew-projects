<?php

namespace WezomCms\Core\Contracts;

interface SitemapXmlGeneratorInterface
{
    /**
     * Start xml writer.
     *
     * @return SitemapXmlGeneratorInterface
     */
    public function start(): SitemapXmlGeneratorInterface;

    /**
     * @param  string|array|mixed  $url
     * @return SitemapXmlGeneratorInterface
     * @throws \ErrorException
     */
    public function add($url): SitemapXmlGeneratorInterface;

    /**
     * @param  array|string  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return SitemapXmlGeneratorInterface
     */
    public function addLocalizedRoute($name, $parameters = [], $absolute = true): SitemapXmlGeneratorInterface;

    /**
     * Finish and write to file.
     *
     * @return void
     */
    public function save();
}
