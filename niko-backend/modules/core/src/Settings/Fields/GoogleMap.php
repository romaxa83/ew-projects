<?php

namespace WezomCms\Core\Settings\Fields;

use WezomCms\Core\Settings\RenderSettings;

class GoogleMap extends AbstractField
{
    /**
     * @var bool
     */
    protected $isMultiple = false;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var array
     */
    protected $center = [];

    /**
     * GoogleMap constructor.
     * @param  RenderSettings|null  $renderSettings
     */
    public function __construct(?RenderSettings $renderSettings = null)
    {
        parent::__construct($renderSettings);

        $this->type = static::TYPE_GOOGLE_MAP;

        $this->height = config('cms.core.main.map.height');

        $this->center = config('cms.core.main.map.coordinates');
    }

    /**
     * @param  bool  $multiple
     * @return GoogleMap
     */
    public function setIsMultiple(bool $multiple = true): GoogleMap
    {
        $this->isMultiple = $multiple;

        return $this;
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->isMultiple;
    }

    /**
     * @param  int  $height
     * @return GoogleMap
     */
    public function setHeight(int $height): GoogleMap
    {
        $this->height = $height;

        return $this;
    }

    /**
     * @return int
     */
    public function height(): int
    {
        return $this->height;
    }

    /**
     * @param $lat
     * @param $lng
     * @return GoogleMap
     */
    public function setCenter($lat, $lng): GoogleMap
    {
        $this->center = compact('lat', 'lng');

        return $this;
    }

    /**
     * @return array
     */
    public function center(): array
    {
        return $this->center;
    }
}
