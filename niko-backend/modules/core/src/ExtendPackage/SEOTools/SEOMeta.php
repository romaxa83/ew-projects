<?php

namespace WezomCms\Core\ExtendPackage\SEOTools;

use Illuminate\Config\Repository as Config;
use Illuminate\Pagination\Paginator;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use LaravelLocalization;
use Request;

class SEOMeta extends \Artesaos\SEOTools\SEOMeta
{
    /**
     * Page name.
     *
     * @var string
     */
    protected $pageName;

    /**
     * @param  Config  $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);

        $this->config->set('defaults.title', false);
        $this->config->set('defaults.description', false);
        $this->config->set('defaults.url', null);
        $this->config->set('defaults.type', 'website');
    }

    /**
     * Sets page name.
     *
     * @param  string  $name
     *
     * @return SEOMeta
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
     * Takes the default title.
     *
     * @return string
     */
    public function getDefaultTitle()
    {
        return null;
    }

    /**
     * Takes the title formatted for display.
     *
     * @return string
     */
    public function getTitle()
    {
        $title = $this->title;

        if (!$title) {
            $title = $this->getPageName();
        }

        // Add page number to end of title
        $currentPage = Paginator::resolveCurrentPage();
        if ($currentPage > 1 && app(Route::class)->isFallback === false && app('isBackend') === false) {
            $title .= ', ' . __('cms-core::site.Page: :page', ['page' => $currentPage]);
        }

        return $title;
    }

    /**
     * Get the Meta keywords.
     *
     * @return array
     */
    public function getKeywords()
    {
        // Hide Meta keywords if page > 1
        if (Paginator::resolveCurrentPage() > 1) {
            return [];
        }

        return parent::getKeywords();
    }

    /**
     * Get the Meta description.
     *
     * @return string|null
     */
    public function getDescription()
    {
        // Hide Meta description if page > 1
        if (Paginator::resolveCurrentPage() > 1) {
            return null;
        }

        return parent::getDescription();
    }

    /**
     * Get all metatags.
     *
     * @return array
     */
    public function getMetatags()
    {
        $metaTags = parent::getMetatags();

        if (
            (config('cms.core.seo.robots.override', false) || !array_key_exists('robots', $metaTags))
            && Request::hasAny(config('cms.core.seo.robots.nofollow.get_params', []))
        ) {
            $metaTags['robots'] = ['name', 'noindex, nofollow'];
        }

        return $metaTags;
    }

    /**
     * Generates meta tags.
     *
     * @param  bool  $minify
     *
     * @return string
     */
    public function generate($minify = false)
    {
        $tag = app('seotools')->getTagsForCurrentLink();
        if ($tag && app(Route::class)->isFallback == false) {
            $this->setTitle($tag->title);
            $this->setDescription($tag->description);
            $this->setKeywords($tag->keywords);
        }

        $this->loadWebMasterTags();

        $title = $this->getTitle();
        $description = $this->getDescription();
        $keywords = $this->getKeywords();
        $metatags = $this->getMetatags();
        $canonical = $this->getCanonical();
        $amphtml = $this->getAmpHtml();
        $prev = $this->getPrev();
        $next = $this->getNext();
        $languages = $this->getAlternateLanguages();

        $html = [];

        if ($title) {
            $html[] = "<title>$title</title>";
        }

        if ($description) {
            $description = e($description);
            $html[] = "<meta name=\"description\" content=\"{$description}\">";
        }

        if (!empty($keywords)) {
            $keywords = e(implode(', ', $keywords));
            $html[] = "<meta name=\"keywords\" content=\"{$keywords}\">";
        }

        foreach ($metatags as $key => $value) {
            $name = $value[0];
            $content = $value[1];

            // if $content is empty jump to nest
            if (empty($content)) {
                continue;
            }

            $content = e($content);
            $html[] = "<meta {$name}=\"{$key}\" content=\"{$content}\">";
        }

        if ($canonical) {
            $canonical = e($canonical);
            $html[] = "<link rel=\"canonical\" href=\"{$canonical}\"/>";
        }

        if ($amphtml) {
            $amphtml = e($amphtml);
            $html[] = "<link rel=\"amphtml\" href=\"{$amphtml}\"/>";
        }

        if ($prev) {
            $prev = e($prev);
            $html[] = "<link rel=\"prev\" href=\"{$prev}\"/>";
        }

        if ($next) {
            $next = e($next);
            $html[] = "<link rel=\"next\" href=\"{$next}\"/>";
        }

        foreach ($languages as $lang) {
            $hrefLang = e($lang['lang']);
            $url = e($lang['url']);
            $html[] = "<link rel=\"alternate\" hreflang=\"{$hrefLang}\" href=\"{$url}\"/>";
        }

        return ($minify) ? implode('', $html) : implode(PHP_EOL, $html);
    }

    /**
     * Get the canonical URL.
     *
     * @return string
     */
    public function getCanonical()
    {
        if (!$this->canonical && \Route::current() && Request::has('page')) {
            return $this->removeFragment(Paginator::resolveCurrentPath());
        }

        return $this->removeFragment($this->canonical ?: app('url')->full());
    }

    /**
     * Get the prev URL.
     *
     * @return string
     */
    public function getPrev()
    {
        return rtrim(str_replace('page=1', '', $this->removeFragment($this->prev)), '?');
    }

    /**
     * Get the next URL.
     *
     * @return string
     */
    public function getNext()
    {
        return $this->removeFragment($this->next);
    }

    /**
     * Get alternate languages.
     *
     * @return array
     * @throws \Mcamara\LaravelLocalization\Exceptions\SupportedLocalesNotDefined
     * @throws \Mcamara\LaravelLocalization\Exceptions\UnsupportedLocaleException
     */
    public function getAlternateLanguages()
    {
        if (!empty($this->alternateLanguages)) {
            return $this->alternateLanguages;
        }

        return collect(LaravelLocalization::getSwitchingLinks())
            ->map(function ($item, $lang) {
                return ['lang' => $lang, 'url' => $item['url']];
            })->values()->all();
    }

    /**
     * @param  string|null  $url
     * @return string|null
     */
    private function removeFragment(?string $url)
    {
        return Str::contains($url, '#') ? Str::before($url, '#') : $url;
    }
}
