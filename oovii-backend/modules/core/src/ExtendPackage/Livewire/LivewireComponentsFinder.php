<?php

namespace WezomCms\Core\ExtendPackage\Livewire;

class LivewireComponentsFinder extends \Livewire\LivewireComponentsFinder
{
    /**
     * @return $this|LivewireComponentsFinder
     * @throws \Exception
     */
    public function build()
    {
        // Build App components.
        if ($this->files->exists($this->path)) {
            $this->manifest = $this->getClassNames()
                ->mapWithKeys(function ($class) {
                    return [$class::getName() => $class];
                })->toArray();
        }

        // Add component aliases.
        event('livewire:discover');

        $this->manifest = array_merge((array)$this->manifest, app('livewire')->getComponentAliases());

        $this->write($this->manifest);

        return $this;
    }

    /**
     * @return bool
     */
    public function componentsIsCached(): bool
    {
        return file_exists($this->manifestPath);
    }
}
