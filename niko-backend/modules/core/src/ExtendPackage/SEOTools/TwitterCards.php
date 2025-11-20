<?php

namespace WezomCms\Core\ExtendPackage\SEOTools;

use Illuminate\Routing\Route;

class TwitterCards extends \Artesaos\SEOTools\TwitterCards
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
     * @return TwitterCards
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
     * @param  bool  $minify
     *
     * @return string
     */
    public function generate($minify = false)
    {
        // Set title from page name field if title is empty.
        if (!array_get($this->values, 'title') && ($pageName = $this->getPageName())) {
            $this->setTitle($pageName);
        }

        $this->overrideMeta();

        $defaultImage = config('cms.core.main.og_image');
        if ($defaultImage && !array_has($this->values, 'image')) {
            $this->setImage(url($defaultImage));
        }

        $this->eachValue($this->values);
        $this->eachValue($this->images, 'images');

        return ($minify) ? implode('', $this->html) : implode(PHP_EOL, $this->html);
    }

    /**
     * Make tags.
     *
     * @param  array  $values
     * @param  null|string  $prefix
     *
     * @internal param array $properties
     */
    protected function eachValue(array $values, $prefix = null)
    {
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $this->eachValue($value, $key);
            } else {
                if (is_numeric($key)) {
                    $key = $prefix . $key;
                } elseif (is_string($prefix)) {
                    $key = $prefix . ':' . $key;
                }

                $this->html[] = $this->makeTag($key, $value);
            }
        }
    }

    /**
     * @param  string  $key
     * @param $value
     *
     * @return string
     *
     * @internal param string $values
     */
    private function makeTag($key, $value)
    {
        return '<meta name="' . $this->prefix . strip_tags($key) . '" content="' . e(strip_tags($value)) . '" />';
    }

    /**
     * Override meta tags by current link.
     */
    private function overrideMeta()
    {
        /** @var SeoLink|false $tag */
        $tag = app('seotools')->getTagsForCurrentLink();
        if ($tag && app(Route::class)->isFallback == false) {
            $this->setTitle($tag->title);
            $this->setDescription($tag->description);
        }
    }
}
