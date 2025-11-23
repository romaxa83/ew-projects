<?php

namespace WezomCms\Core\ExtendPackage;

use Illuminate\Support\Str;

class PackageManifest extends \Illuminate\Foundation\PackageManifest
{
    /**
     * Get all of the service provider class names for all packages.
     *
     * @return array
     */
    public function providers()
    {
        $items = collect($this->getManifest())->flatMap(function ($configuration) {
            return (array) ($configuration['providers'] ?? []);
        })->filter();

        $partitions = $items->partition(function ($provider) {
            return Str::startsWith($provider, 'WezomCms\\');
        });

        $wezomCms = $partitions->shift()->sortBy(function ($a) {
            if (Str::contains($a, 'CoreServiceProvider')) {
                return -1;
            } elseif (Str::contains($a, 'UiServiceProvider')) {
                return 0;
            } else {
                return 1;
            }
        });

        return $partitions->push($wezomCms)->flatten()->all();
    }
}
