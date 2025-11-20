<?php

namespace WezomCms\Core\Contracts\Assets;

use Illuminate\Contracts\Support\Htmlable;

interface AssetItemInterface extends Htmlable
{
    public const TYPE_JS = 'js';
    public const TYPE_CSS = 'css';

    /**
     * @param  string  $name
     * @return self
     */
    public function setName(string $name): AssetItemInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param  string  $content
     * @return self
     */
    public function setContent(string $content): AssetItemInterface;

    /**
     * @return string
     */
    public function getContent(): string;

    /**
     * @param  iterable  $items
     * @return self
     */
    public function setAttributes(iterable $items): AssetItemInterface;

    /**
     * @return iterable
     */
    public function getAttributes(): iterable;

    /**
     * @param  string  $position
     * @return AssetItemInterface
     */
    public function setPosition(string $position): AssetItemInterface;

    /**
     * @return string
     */
    public function getPosition(): string;

    /**
     * Set sort position.
     *
     * @param  int  $sort
     * @return AssetItemInterface
     */
    public function setSort(int $sort): AssetItemInterface;

    /**
     * @return string
     */
    public function getSort(): string;

    /**
     * @param  string  $type
     * @return AssetItemInterface
     */
    public function setType(string $type): AssetItemInterface;

    /**
     * @return string
     */
    public function getType(): string;
}
