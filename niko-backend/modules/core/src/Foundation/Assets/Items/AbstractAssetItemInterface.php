<?php

namespace WezomCms\Core\Foundation\Assets\Items;

use WezomCms\Core\Contracts\Assets\AssetItemInterface;

abstract class AbstractAssetItemInterface implements AssetItemInterface
{
    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var string
     */
    protected $position = '';

    /**
     * @var int
     */
    protected $sort = 0;

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @param  string  $name
     * @return self
     */
    public function setName(string $name): AssetItemInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param  string  $content
     * @return self
     */
    public function setContent(string $content): AssetItemInterface
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @param  iterable  $items
     * @return self
     */
    public function setAttributes(iterable $items): AssetItemInterface
    {
        $this->attributes = $items;

        return $this;
    }

    /**
     * @return iterable
     */
    public function getAttributes(): iterable
    {
        return $this->attributes;
    }

    /**
     * @param  string  $position
     * @return AssetItemInterface
     */
    public function setPosition(string $position): AssetItemInterface
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * Set sort position.
     *
     * @param  int  $sort
     * @return AssetItemInterface
     */
    public function setSort(int $sort): AssetItemInterface
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return string
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @param  string  $type
     * @return AssetItemInterface
     */
    public function setType(string $type): AssetItemInterface
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     * @throws \Exception
     */
    public function toHtml()
    {
        $attributes = $this->combineAttributes();

        switch ($this->type) {
            case self::TYPE_JS:
                return '<script src="' . $this->getContent() . '" ' . $attributes . '></script>';
            case self::TYPE_CSS:
                return '<link rel="stylesheet" href="' . $this->getContent() . '" ' . $attributes . '>';
            default:
                throw new \Exception(sprintf('Source type [%s] not allowed', $this->type));
        }
    }

    /**
     * Get the HTML string.
     *
     * @return string
     * @throws \Exception
     */
    public function __toString()
    {
        return $this->toHtml();
    }

    /**
     * @return string
     */
    protected function combineAttributes()
    {
        $result = '';

        $attributes = array_filter($this->attributes);
        foreach ($attributes as $name => $value) {
            $result .= sprintf(' %s="%s"', e($name), e($value));
        }

        return $result;
    }
}
