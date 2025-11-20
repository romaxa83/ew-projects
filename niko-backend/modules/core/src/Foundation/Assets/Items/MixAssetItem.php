<?php

namespace WezomCms\Core\Foundation\Assets\Items;

class MixAssetItem extends AbstractAssetItemInterface
{
    /**
     * @return string
     */
    public function getContent(): string
    {
        try {
            return mix($this->content);
        } catch (\Exception $e) {
            return '';
        }
    }
}
