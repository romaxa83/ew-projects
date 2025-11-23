<?php

namespace WezomCms\Core\ExtendPackage\SEOTools;

use Illuminate\Contracts\Pagination\Paginator as PaginatorContract;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Route;
use WezomCms\Core\Enums\SeoFields;
use WezomCms\Core\Image\Exceptions\IncorrectImageSizeException;
use WezomCms\Core\Image\ImageService;
use WezomCms\Core\Traits\Model\ImageAttachable;
use WezomCms\Core\Traits\OGImageTrait;

class SEOTools extends \Artesaos\SEOTools\SEOTools
{
    use OGImageTrait;

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
     * @param  array|object|ImageAttachable  $data
     * @param  bool  $setText
     * @param  string|false  $imageField - If false is specified - the OG image will not be installed.
     * @return $this
     */
    public function fill($data, bool $setText = true, $imageField = ImageService::IMAGE)
    {
        $this->setPageName(data_get($data, SeoFields::NAME))
            ->setTitle(data_get($data, SeoFields::TITLE))
            ->setH1(data_get($data, SeoFields::H1))
            ->setDescription(data_get($data, SeoFields::DESCRIPTION))
            ->when($setText, function () use ($data) {
                $this->setSeoText(data_get($data, SeoFields::TEXT));
            })
            ->metatags()
            ->setKeywords(data_get($data, SeoFields::KEYWORDS));

        if (is_object($data) && $imageField !== false && method_exists($data, 'imageSettings')) {
            try {
                $this->setOGImage($data, $imageField);
            } catch (IncorrectImageSizeException $e) {
                report($e);
            }
        }

        return $this;
    }

    /**
     * @param  PaginatorContract  $paginator
     * @return $this
     */
    public function setPrevNext(PaginatorContract $paginator)
    {
        $this->metatags()
            ->setNext($paginator->nextPageUrl())
            ->setPrev($paginator->previousPageUrl());

        return $this;
    }

    /**
     * @param  string|null  $value
     * @return $this
     */
    public function noIndex(string $value = null)
    {
        $this->metatags()->addMeta('robots', $value ?? config('cms.core.seo.robots.noindex.default'));

        return $this;
    }

    /**
     * @param $value
     * @param  callable  $callback
     * @return $this|SEOTools
     */
    public function when($value, callable $callback)
    {
        if ($value) {
            return $callback($this, $value) ?: $this;
        }

        return $this;
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
            $this->setCanonical(null);
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
