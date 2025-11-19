<?php

namespace Wezom\Core\ExtendPackage;

use Exception;
use Illuminate\Support\Collection;

class PackageManifest extends \Illuminate\Foundation\PackageManifest
{
    protected const VENDOR_NAME = 'wezom';

    /**
     * Build the manifest and write it to disk.
     *
     * @return void
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws Exception
     */
    public function build()
    {
        $packages = [];

        if ($this->files->exists($path = $this->vendorPath . '/composer/installed.json')) {
            $installed = json_decode($this->files->get($path), true);

            $packages = $installed['packages'] ?? $installed;
        }

        $packages = $this->sortPackages($packages);

        $ignoreAll = in_array('*', $ignore = $this->packagesToIgnore());

        $this->write(collect($packages)->mapWithKeys(function ($package) {
            return [$this->format($package['name']) => $package['extra']['laravel'] ?? []];
        })->each(function ($configuration) use (&$ignore) {
            $ignore = array_merge($ignore, $configuration['dont-discover'] ?? []);
        })->reject(function ($configuration, $package) use ($ignore, $ignoreAll) {
            return $ignoreAll || in_array($package, $ignore);
        })->filter()->all());
    }

    protected function sortPackages(array $packages): array
    {
        /**
         * @var Collection $cms
         * @var Collection $other
         */
        [$cms, $other] = collect($packages)
            ->partition(fn (array $package) => str_starts_with($package['name'], static::VENDOR_NAME));

        $data = $cms->values()->all();

        foreach ($data as $package) {
            $this->recursiveSort($data, $package['name']);
        }

        return $other->merge($data)->all();
    }

    protected function recursiveSort(array &$data, string $name): void
    {
        $package = array_first($data, fn (array $package) => $package['name'] === $name);

        if ($package['sort_is_set'] ?? false) {
            return;
        }

        $dependencies = array_filter(
            $package['require'] ?? [],
            fn ($name) => str_starts_with($name, static::VENDOR_NAME),
            ARRAY_FILTER_USE_KEY
        );

        if (!$dependencies) {
            $data[$this->getPackageIndex($data, $package['name'])]['sort_is_set'] = true;

            return;
        }

        foreach (array_keys($dependencies) as $dependency) {
            $this->recursiveSort($data, $dependency);
        }

        // remove current item position
        unset($data[$this->getPackageIndex($data, $package['name'])]);

        $lastPosition = $this->getLastPosition($data, $dependencies);

        $package['sort_is_set'] = true;

        array_splice($data, $lastPosition + 1, 0, [$package]);
    }

    protected function getLastPosition(array $data, array $dependencies): mixed
    {
        $positions = [];
        foreach (array_keys($dependencies) as $item) {
            $positions[$item] = $this->getPackageIndex($data, $item);
        }

        // get last item position
        asort($positions);

        return end($positions);
    }

    protected function getPackageIndex(array $data, string $name): ?int
    {
        foreach ($data as $index => $sub) {
            if ($sub['name'] === $name) {
                return $index;
            }
        }

        return null;
    }
}
