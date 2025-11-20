<?php

namespace WezomCms\Core\Settings;

class RenderSettings
{
    public const SIDE_NONE = 'none';
    public const SIDE_LEFT = 'left';
    public const SIDE_RIGHT = 'right';

    /**
     * @var string
     */
    protected $side;

    /**
     * @var Tab
     */
    protected $tab;

    /**
     * RenderSettings constructor.
     * @param  Tab  $tab
     * @param  string  $side
     * @throws \Exception
     */
    public function __construct(Tab $tab, string $side = RenderSettings::SIDE_NONE)
    {
        if (!in_array($side, [static::SIDE_NONE, static::SIDE_LEFT, static::SIDE_RIGHT])) {
            throw new \Exception("Side '{$side}' Not allowed!");
        }

        $this->tab = $tab;
        $this->side = $side;
    }

    /**
     * @param  string  $side
     * @param  int  $sort
     * @return RenderSettings
     * @throws \Exception
     */
    public static function adminTab(string $side = RenderSettings::SIDE_NONE, int $sort = 10): RenderSettings
    {
        return new RenderSettings(new Tab('admin', __('cms-core::admin.layout.Admin panel'), $sort, 'fa-lock'), $side);
    }

    /**
     * @param  string  $side
     * @param  int  $sort
     * @return RenderSettings
     * @throws \Exception
     */
    public static function siteTab(string $side = RenderSettings::SIDE_NONE, int $sort = 1): RenderSettings
    {
        return new RenderSettings(new Tab('site', __('cms-core::admin.layout.Site'), $sort, 'fa-sitemap'), $side);
    }

    /**
     * @return Tab
     */
    public function getTab(): Tab
    {
        return $this->tab;
    }

    /**
     * @return string
     */
    public function getSide(): string
    {
        return $this->side;
    }
}
