<?php

namespace WezomCms\Core\Foundation\Assets\Items;

use WezomCms\Core\Contracts\Assets\AssetManagerInterface;

class LocalFileAssetItem extends AbstractAssetItemInterface
{
    /**
     * @return string
     */
    public function getContent(): string
    {
        return app(AssetManagerInterface::class)->addVersion($this->content);
    }
}
