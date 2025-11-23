<?php

namespace WezomCms\Core\Settings\Fields;

class Slug extends AbstractField
{
    protected $slugSource;

    /**
     * @return string
     */
    final public function getType(): string
    {
        return static::TYPE_SLUG;
    }

    /**
     * @param  string  $slugSource
     * @return Slug
     */
    public function setSlugSource(string $slugSource): Slug
    {
        $this->slugSource = $slugSource;

        return $this;
    }

    /**
     * @param  null  $locale
     * @return mixed
     */
    public function getSlugSourceName($locale = null)
    {
        return str_replace('{locale}', $locale, $this->slugSource);
    }
}
