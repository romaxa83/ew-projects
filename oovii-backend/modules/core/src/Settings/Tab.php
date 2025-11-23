<?php

namespace WezomCms\Core\Settings;

class Tab
{
    protected $key;
    protected $name;
    protected $sort;
    protected $icon;

    /**
     * Tab constructor.
     * @param  string  $key
     * @param $name
     * @param  int  $sort
     * @param  null  $icon
     */
    public function __construct(string $key, $name = null, $sort = 0, $icon = null)
    {
        $this->key = $key;
        $this->name = $name;
        $this->sort = (int) $sort;
        $this->icon = $icon;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  mixed  $name
     * @return Tab
     */
    public function setName($name): Tab
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    /**
     * @param  int  $sort
     * @return Tab
     */
    public function setSort(int $sort): Tab
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @param  null  $icon
     * @return Tab
     */
    public function setIcon($icon): Tab
    {
        $this->icon = $icon;

        return $this;
    }
}
