<?php

namespace WezomCms\Core\ExtendPackage\SEOTools;

use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Route;

class SEOTools extends \Artesaos\SEOTools\SEOTools
{
    /**
     * Seo text.
     *
     * @var string
     */
    protected $seoText;

    /**
     * Page heading (H1)
     *
     * @var string
     */
    protected $h1;

    /**
     * Object with meta tags found in DB by current url.
     *
     * @var mixed
     */
    protected $tagsForSpecificLinks = false;

    /**
     * Setup page name for all seo providers.
     *
     * @param  string  $name
     *
     * @return \Artesaos\SEOTools\Contracts\SEOTools
     */
    public function setPageName($name)
    {
        $this->metatags()->setPageName($name);
        $this->opengraph()->setPageName($name);
        $this->twitter()->setPageName($name);

        return $this;
    }

    /**
     * Get current page name from metatags.
     *
     * @return string
     */
    public function getPageName()
    {
        return $this->metatags()->getPageName();
    }

    /**
     * Sets H1.
     *
     * @param  string  $h1
     *
     * @return SEOTools
     */
    public function setH1($h1)
    {
        $this->h1 = $h1;

        return $this;
    }

    /**
     * Get H1.
     *
     * @return string|null
     */
    public function getH1()
    {
        $this->overrideMeta();

        if ($this->h1) {
            return $this->h1;
        }

        return $this->getPageName();
    }

    /**
     * Sets seo text.
     *
     * @param  string  $text
     *
     * @return SEOTools
     */
    public function setSeoText($text)
    {
        $this->seoText = $text;

        return $this;
    }

    /**
     * Get seo text.
     *
     * @return string|null
     */
    public function getSeoText()
    {
        // Hide Seo text if page > 1
        if (Paginator::resolveCurrentPage() > 1) {
            return null;
        }

        $this->overrideMeta();

        return $this->seoText;
    }

    /**
     * Generate from all seo providers.
     *
     * @param  bool  $minify
     *
     * @return string
     */
    public function generate($minify = false)
    {
        event('seo_tools:before_generate', $this);

        return parent::generate($minify);
    }

    /**
     * Override meta tags by current link.
     */
    public function overrideMeta()
    {
        $tag = $this->getTagsForCurrentLink();
        if ($tag && app(Route::class)->isFallback == false) {
            $this->setH1($tag->h1);
            $this->setSeoText($tag->seo_text);
        }
    }

    /**
     * @return mixed
     */
    public function getTagsForCurrentLink()
    {
        if (false === $this->tagsForSpecificLinks) {
            $tags = event('seo_tools:get_tags_for_current_link') ?? [];
            $this->tagsForSpecificLinks = array_first($tags);
        }

        return $this->tagsForSpecificLinks;
    }
}
