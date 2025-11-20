<?php

namespace WezomCms\Core\Widgets;

use WezomCms\Core\Contracts\Assets\AssetItemInterface;
use WezomCms\Core\Contracts\Assets\AssetManagerInterface;
use WezomCms\Core\Foundation\Widgets\AbstractWidget;

class AdminAssets extends AbstractWidget
{
    /**
     * View name.
     *
     * @var string
     */
    protected $view = 'cms-core::admin.widgets.assets';

    /**
     * @var bool
     */
    protected static $registered = false;

    /**
     * @param  AssetManagerInterface  $assetManager
     * @return array|null
     */
    public function execute(AssetManagerInterface $assetManager): ?array
    {
        if (!static::$registered) {
            $this->registerAssets($assetManager);
            static::$registered = true;
        }

        return compact('assetManager');
    }

    /**
     * @param  AssetManagerInterface  $assetManager
     */
    private function registerAssets(AssetManagerInterface $assetManager)
    {
        // Styles
        foreach (config('cms.core.assets.styles', []) as $index => $style) {
            if (is_string($style)) {
                $assetManager->addCss($this->replaceAssetLocales($style));
            } else {
                $content = array_get($style, 'content', '');
                $assetManager->addCss(
                    $content instanceof AssetItemInterface ? $content : $this->replaceAssetLocales($content),
                    array_get($style, 'name', ''),
                    array_get($style, 'attributes', [])
                );

                if ($position = array_get($style, 'position')) {
                    $assetManager->position($position);
                }

                $assetManager->setSort(-100 + $index);
            }
        }

        // Scripts
        $iteration = 0;
        foreach (config('cms.core.assets.scripts', []) as $position => $scripts) {
            foreach ($scripts as $script) {
                /** @var $script string|AssetManagerInterface|array */
                if (is_string($script)) {
                    $assetManager->addJs($this->replaceAssetLocales($script));
                } else {
                    $content = array_get($script, 'content', '');
                    $assetManager->addJs(
                        $script instanceof AssetItemInterface ? $script : $this->replaceAssetLocales($content),
                        array_get($script, 'name', ''),
                        array_get($script, 'attributes', [])
                    );
                }

                $assetManager->position(array_get($script, 'position', $position))
                    ->setSort(-100 + $iteration++);
            }
        }
    }

    /**
     * @param  string  $url
     * @return mixed
     */
    private function replaceAssetLocales(string $url)
    {
        $locale = app()->getLocale();

        $modifiedLocale = str_replace(['en'], ['en-GB'], $locale);

        return str_replace(['{original-locale}', '{locale}'], [$locale, $modifiedLocale], $url);
    }
}
