<?php

namespace WezomCms\Core\Settings;

use Illuminate\Support\Collection;
use WezomCms\Core\Settings\Fields\AbstractField;

class MultilingualGroup
{
    /**
     * @var Collection|AbstractField[]
     */
    private $items;

    private $sort;

    /**
     * @var null|RenderSettings
     */
    private $renderSettings;

    /**
     * MultilingualGroup constructor.
     * @param  null|RenderSettings  $renderSettings
     * @param  iterable  $items
     * @param  int  $sort
     * @throws \Exception
     */
    public function __construct(?RenderSettings $renderSettings = null, iterable $items = [], int $sort = 0)
    {
        $this->renderSettings = $renderSettings;

        $this->items = new Collection();

        foreach ($items as $item) {
            $this->addItem($item);
        }

        $this->sort = $sort;
    }

    /**
     * @param  null|RenderSettings  $renderSettings
     * @param  iterable  $items
     * @param  int  $sort
     * @return MultilingualGroup
     * @throws \Exception
     */
    public static function make(?RenderSettings $renderSettings = null, iterable $items = [], int $sort = 0)
    {
        return new static($renderSettings, $items, $sort);
    }

    /**
     * @param  RenderSettings  $renderSettings
     * @return MultilingualGroup
     */
    public function setRenderSettings(RenderSettings $renderSettings): MultilingualGroup
    {
        $this->renderSettings = $renderSettings;

        return $this;
    }

    /**
     * @return RenderSettings
     */
    public function getRenderSettings(): RenderSettings
    {
        return $this->renderSettings;
    }

    /**
     * @param  AbstractField  $item
     * @return MultilingualGroup
     * @throws \Exception
     */
    public function addItem(AbstractField $item)
    {
        if (!$item->isMultilingual()) {
            throw new \Exception("Item {$item->getName()} is not multilingual!");
        }

        $this->items->push($item);

        return $this;
    }

    /**
     * @return Collection|AbstractField[]
     */
    public function getItems()
    {
        return $this->items->sortBy(function (AbstractField $item) {
            return $item->getSort();
        });
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }
}
