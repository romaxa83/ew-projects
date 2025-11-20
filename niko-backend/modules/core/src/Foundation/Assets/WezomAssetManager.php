<?php

namespace WezomCms\Core\Foundation\Assets;

use Illuminate\Support\Collection;
use WezomCms\Core\Contracts\Assets\AssetItemInterface;
use WezomCms\Core\Contracts\Assets\AssetManagerInterface;
use WezomCms\Core\Foundation\Assets\Items\ExternalAssetItem;
use WezomCms\Core\Foundation\Assets\Items\InlineAssetItem;
use WezomCms\Core\Foundation\Assets\Items\LocalFileAssetItem;

class WezomAssetManager implements AssetManagerInterface
{
    /**
     * @var string - Default position for added script/style
     */
    protected $defaultPosition = self::POSITION_DEFAULT;

    /**
     * @var Collection
     */
    protected $js;

    /**
     * @var Collection
     */
    protected $css;

    /**
     * @var null|AssetItemInterface
     */
    protected $last;

    public function __construct()
    {
        $this->js = new Collection();
        $this->css = new Collection();
    }

    /**
     * @param  string|AssetItemInterface  $path
     * @param  string  $name
     * @param  array  $attributes
     * @return $this
     */
    public function addJs($path, string $name = '', array $attributes = [])
    {
        if ($path instanceof AssetItemInterface) {
            $item = $path;
            $item->setType(AssetItemInterface::TYPE_JS);
        } else {
            $item = $this->createItem(AssetItemInterface::TYPE_JS, $path, $name, $attributes);
        }
        if (!$item->getPosition()) {
            $item->setPosition(AssetManagerInterface::POSITION_END_BODY);
        }

        $this->last = $item;

        $this->js->push($item);

        return $this;
    }

    /**
     * @param  string|AssetItemInterface  $path
     * @param  string  $name
     * @param  array  $attributes
     * @return $this
     */
    public function addCss($path, string $name = '', array $attributes = [])
    {
        if ($path instanceof AssetItemInterface) {
            $item = $path;
            $item->setType(AssetItemInterface::TYPE_CSS);
        } else {
            $item = $this->createItem(AssetItemInterface::TYPE_CSS, $path, $name, $attributes);
        }
        if (!$item->getPosition()) {
            $item->setPosition($this->defaultPosition);
        }

        $this->last = $item;

        $this->css->push($item);

        return $this;
    }

    /**
     * @param  string  $script
     * @param  string  $name
     * @param  array  $attributes
     * @return $this
     */
    public function addInlineScript(string $script, string $name = '', array $attributes = [])
    {
        $script = preg_replace('#\<\/?script.*?\>#', '', $script);
        $item = $this->createInlineItem(AssetItemInterface::TYPE_JS, $script, $name, $attributes);

        $this->last = $item;

        $this->js->push($item);

        return $this;
    }

    /**
     * @param  string  $style
     * @param  string  $name
     * @param  array  $attributes
     * @return $this
     */
    public function addInlineStyle(string $style, string $name = '', array $attributes = [])
    {
        $item = $this->createInlineItem(AssetItemInterface::TYPE_CSS, $style, $name, $attributes);

        $this->last = $item;

        $this->css->push($item);

        return $this;
    }

    /**
     * @param  string|null  $position
     * @return \ArrayAccess|AssetItemInterface[]
     */
    public function getJs(string $position = null)
    {
        $position = $position ?: $this->defaultPosition;

        event(sprintf('assets: js-%s-%s', app('side'), $position));

        return $this->js->filter(function (AssetItemInterface $item) use ($position) {
            return $item->getPosition() === $position;
        })->sortBy(function (AssetItemInterface $item) {
            return $item->getSort();
        });
    }

    /**
     * @param  string|null  $position
     * @return \ArrayAccess
     */
    public function getCss(string $position = null)
    {
        $position = $position ?: $this->defaultPosition;

        return $this->css->filter(function (AssetItemInterface $item) use ($position) {
            return $item->getPosition() === $position;
        })->sortBy(function (AssetItemInterface $item) {
            return $item->getSort();
        });
    }

    /**
     * @param  string|null  $position
     * @return \ArrayAccess
     */
    public function getInlineScripts(string $position = null)
    {
        $position = $position ?: $this->defaultPosition;

        return $this->js->filter(function (AssetItemInterface $item) use ($position) {
            return ($item instanceof InlineAssetItem and $item->getPosition() === $position);
        })->sortBy(function (AssetItemInterface $item) {
            return $item->getSort();
        });
    }


    /**
     * @param  string|null  $position
     * @return \ArrayAccess
     */
    public function getInlineStyles(string $position = null)
    {
        $position = $position ?: $this->defaultPosition;

        return $this->css->filter(function (AssetItemInterface $item) use ($position) {
            return ($item instanceof InlineAssetItem and $item->getPosition() === $position);
        })->sortBy(function (AssetItemInterface $item) {
            return $item->getSort();
        });
    }

    /**
     * @param  string  $position
     * @return AssetManagerInterface
     * @throws \Exception
     */
    public function position(string $position): AssetManagerInterface
    {
        $last = $this->last;

        if (!$last instanceof AssetItemInterface) {
            throw new \LogicException('No last asset item for moving');
        }

        switch ($last->getType()) {
            case AssetItemInterface::TYPE_JS:
                $collection = $this->js;
                break;
            case AssetItemInterface::TYPE_CSS:
                $collection = $this->css;
                break;
            default:
                throw new \InvalidArgumentException('Not available asset type');
        }

        $key = $collection->search($last);

        /** @var AssetItemInterface $assetItem */
        $assetItem = $collection->get($key);
        $assetItem->setPosition($position);

        return $this;
    }

    /**
     * Load once js/css with same name.
     *
     * @return AssetManagerInterface
     */
    public function once(): AssetManagerInterface
    {
        $last = $this->last;

        if (!$last instanceof AssetItemInterface) {
            throw new \LogicException('No last asset item for moving');
        }

        switch ($last->getType()) {
            case AssetItemInterface::TYPE_JS:
                $this->js = $this->js->filter(function (AssetItemInterface $item) use ($last) {
                    return $item->getName() === '' || ($item->getName() === $last->getName() && $item === $last);
                });
                break;
            case AssetItemInterface::TYPE_CSS:
                $this->css = $this->css->filter(function (AssetItemInterface $item) use ($last) {
                    return $item->getName() === '' || ($item->getName() === $last->getName() && $item === $last);
                });
                break;
            default:
                throw new \InvalidArgumentException('Not available asset type');
        }

        return $this;
    }

    /**
     * Set sort position.
     *
     * @param  int  $sort
     * @return AssetManagerInterface
     */
    public function setSort(int $sort): AssetManagerInterface
    {
        $last = $this->last;

        if (!$last instanceof AssetItemInterface) {
            throw new \LogicException('No last asset item for moving');
        }

        switch ($last->getType()) {
            case AssetItemInterface::TYPE_JS:
                $collection = $this->js;
                break;
            case AssetItemInterface::TYPE_CSS:
                $collection = $this->css;
                break;
            default:
                throw new \InvalidArgumentException('Not available asset type');
        }

        $key = $collection->search($last);

        /** @var AssetItemInterface $assetItem */
        $assetItem = $collection->get($key);
        $assetItem->setSort($sort);

        return $this;
    }

    /**
     * Add last modify time as query string parameter.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    public function addVersion(string $path, $secure = null): string
    {
        $basePath = parse_url($path, PHP_URL_PATH);

        if (is_file(public_path($basePath))) {
            $time = filemtime(public_path($basePath));
            if ($time) {
                $path = $basePath . '?v=' . $time;
            }
        }

        return app('url')->asset($path, $secure);
    }

    /**
     * @param  string  $type
     * @param  string  $content
     * @param  string  $name
     * @param  array  $attributes
     * @return LocalFileAssetItem
     */
    private function createItem(string $type, string $content, string $name = '', array $attributes = [])
    {
        // Check external link
        $item = false === strpos($content, '//') ? new LocalFileAssetItem() : new ExternalAssetItem();

        $item->setType($type)
            ->setContent($content)
            ->setName($name)
            ->setAttributes($attributes)
            ->setPosition($this->getPositionByType($type));

        return $item;
    }

    /**
     * @param  string  $type
     * @param  string  $content
     * @param  string  $name
     * @param  array  $attributes
     * @return InlineAssetItem
     */
    private function createInlineItem(string $type, string $content, string $name = '', array $attributes = [])
    {
        $item = new InlineAssetItem();
        $item->setType($type)
            ->setContent($content)
            ->setName($name)
            ->setAttributes($attributes)
            ->setPosition($this->getPositionByType($type));

        return $item;
    }

    /**
     * @param  string  $type
     * @return string
     */
    private function getPositionByType(string $type): string
    {
        switch ($type) {
            case AssetItemInterface::TYPE_JS:
                return AssetManagerInterface::POSITION_END_BODY;
            case AssetItemInterface::TYPE_CSS:
            default:
                return $this->defaultPosition;
        }
    }
}
