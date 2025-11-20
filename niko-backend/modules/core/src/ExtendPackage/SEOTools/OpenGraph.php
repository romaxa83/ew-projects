<?php

namespace WezomCms\Core\ExtendPackage\SEOTools;

use Illuminate\Routing\Route;

class OpenGraph extends \Artesaos\SEOTools\OpenGraph
{
    /**
     * Page name.
     *
     * @var string
     */
    protected $pageName;

    /**
     * Sets the title.
     *
     * @param  string  $name
     *
     * @return OpenGraph
     */
    public function setPageName($name)
    {
        // store name
        $this->pageName = strip_tags($name);

        return $this;
    }

    /**
     * Get page name.
     *
     * @return string
     */
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * Make a og tag.
     *
     * @param  string  $key  meta property key
     * @param  string  $value  meta property value
     * @param  bool  $ogPrefix  opengraph prefix
     *
     * @return string
     */
    protected function makeTag($key = null, $value = null, $ogPrefix = false)
    {
        return sprintf(
            '<meta property="%s%s" content="%s" />%s',
            $ogPrefix ? $this->og_prefix : '',
            strip_tags($key),
            e($value),
            PHP_EOL
        );
    }

    /**
     * Add or update property.
     *
     * @return void
     */
    protected function setupDefaults()
    {
        $this->config['defaults']['title'] = false;
        $this->config['defaults']['description'] = false;
        $this->config['defaults']['url'] = null;
        $this->config['defaults']['type'] = 'website';
        $defaultImage = config('cms.core.main.og_image');
        if ($defaultImage) {
            $this->config['defaults']['images'][] = url($defaultImage);
        }

        // Setup title
        if (!array_get($this->properties, 'title') && ($pageName = $this->getPageName())) {
            $this->setTitle($pageName);
        }

        $this->overrideMeta();

        parent::setupDefaults();
    }

    /**
     * Override meta tags by current link.
     */
    private function overrideMeta()
    {
        /** @var false $tag */
        $tag = app('seotools')->getTagsForCurrentLink();
        if ($tag && app(Route::class)->isFallback === false) {
            $this->setTitle($tag->title);
            $this->setDescription($tag->description);
        }
    }
}
