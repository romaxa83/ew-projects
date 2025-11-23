<?php

namespace WezomCms\Core\Contracts\Assets;

interface AssetManagerInterface
{
    public const POSITION_HEAD = 'head';
    public const POSITION_START_BODY = 'start_body';
    public const POSITION_END_BODY = 'end_body';
    public const POSITION_DEFAULT = self::POSITION_HEAD;

    public const GROUP_ADMIN = 'admin';
    public const GROUP_SITE = 'site';

    /**
     * @param  string|AssetItemInterface  $path
     * @param  string  $name
     * @param  array  $attributes
     * @return $this
     */
    public function addJs($path, string $name = '', array $attributes = []);

    /**
     * @param  string|AssetItemInterface  $path
     * @param  string|null  $name
     * @param  array  $attributes
     * @return $this
     */
    public function addCss($path, string $name = '', array $attributes = []);

    /**
     * @param  string  $script
     * @param  string  $name
     * @param  array  $attributes
     * @return $this
     */
    public function addInlineScript(string $script, string $name = '', array $attributes = []);

    /**
     * @param  string  $style
     * @param  string  $name
     * @param  array  $attributes
     * @return $this
     */
    public function addInlineStyle(string $style, string $name = '', array $attributes = []);

    /**
     * @param  string|null  $group
     * @param  string|null  $position
     * @return \ArrayAccess|AssetItemInterface[]
     */
    public function getCss(?string $group = null, ?string $position = null);

    /**
     * @param  string|null  $group
     * @param  string|null  $position
     * @return \ArrayAccess|AssetItemInterface[]
     */
    public function getJs(?string $group = null, ?string $position = null);

    /**
     * @param  string|null  $group
     * @param  string|null  $position
     * @return \ArrayAccess
     */
    public function getInlineScripts(?string $group = null, ?string $position = null);

    /**
     * @param  string|null  $group
     * @param  string|null  $position
     * @return \ArrayAccess
     */
    public function getInlineStyles(?string $group = null, ?string $position = null);

    /**
     * @param  string  $position
     * @return AssetManagerInterface
     */
    public function position(string $position): AssetManagerInterface;

    /**
     * Set group name for last added element.
     *
     * @param  string  $name
     * @return AssetManagerInterface
     */
    public function group(string $name): AssetManagerInterface;

    /**
     * Load once js/css with same name.
     *
     * @return AssetManagerInterface
     */
    public function once(): AssetManagerInterface;

    /**
     * Set sort position.
     *
     * @param  int  $sort
     * @return AssetManagerInterface
     */
    public function setSort(int $sort): AssetManagerInterface;

    /**
     * Add last modify time as query string parameter.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    public function addVersion(string $path, $secure = null): string;
}
