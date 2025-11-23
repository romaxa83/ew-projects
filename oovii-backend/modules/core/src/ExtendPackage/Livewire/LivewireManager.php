<?php

namespace WezomCms\Core\ExtendPackage\Livewire;

use Livewire\LivewireComponentsFinder;

class LivewireManager extends \Livewire\LivewireManager
{
    /**
     * @param $class
     * @param  null  $default
     * @return int|mixed|string|null
     */
    public function getAlias($class, $default = null)
    {
        $finder = app(LivewireComponentsFinder::class);
        if (!$this->componentAliases && $finder->componentsIsCached()) {
            $this->componentAliases = $finder->getManifest();
        }

        return parent::getAlias($class, $default);
    }
}
