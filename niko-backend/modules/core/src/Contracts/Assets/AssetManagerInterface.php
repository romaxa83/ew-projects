<?php

namespace WezomCms\Core\Contracts\Assets;

interface AssetManagerInterface
{
    public const POSITION_HEAD = 'head';
    public const POSITION_START_BODY = 'start_body';
    public const POSITION_END_BODY = 'end_body';
    public const POSITION_DEFAULT = self::POSITION_HEAD;

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
     * @param  string|null  $position
     * @return \ArrayAccess|AssetItemInterface[]
     */
    public function getJs(string $position = null);

    /**
     * @param  string|null  $position
     * @return \ArrayAccess|AssetItemInterface[]
     */
    public function getCss(string $position = null);

    /**
     * @param  string|null  $position
     * @return \ArrayAccess
     */
    public function getInlineScripts(string $position = null);

    /**
     * @param  string|null  $position
     * @return \ArrayAccess
     */
    public function getInlineStyles(string $position = null);

    /**
     * @param  string  $position
     * @return AssetManagerInterface
     */
    public function position(string $position): AssetManagerInterface;

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
