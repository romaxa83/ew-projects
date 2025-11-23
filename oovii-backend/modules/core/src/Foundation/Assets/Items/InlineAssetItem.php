<?php

namespace WezomCms\Core\Foundation\Assets\Items;

class InlineAssetItem extends AbstractAssetItemInterface
{
    /**
     * Get content as a string of HTML.
     *
     * @return string
     * @throws \Exception
     */
    public function toHtml()
    {
        $attributes = $this->combineAttributes();

        switch ($this->type) {
            case self::TYPE_JS:
                return '<script ' . $attributes . '>' . $this->getContent() . '</script>';
            case self::TYPE_CSS:
                return '<style type="text/css" ' . $attributes . '>' . $this->getContent() . '</style>';
            default:
                throw new \Exception(sprintf('Source type [%s] not allowed', $this->type));
        }
    }
}
