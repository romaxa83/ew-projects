<?php

namespace App\Services\Utilities;

use App\Enums\Utils\Versioning\VersionStatusEnum;
use App\Models\Utils\Version;

class AppVersionService
{
    public function status(string $version): VersionStatusEnum
    {
        $versions = Version::first();

        if (!$versions) {
            return VersionStatusEnum::OK();
        }

        return $this->compare($version, $versions);
    }

    public function compare(string $version, Version $versions): VersionStatusEnum
    {
        if (version_compare($version, $versions->recommended_version, '>=')) {
            return VersionStatusEnum::OK();
        }

        if (version_compare($version, $versions->required_version, '<')) {
            return VersionStatusEnum::UPDATE_REQUIRED();
        }

        return VersionStatusEnum::UPDATE_RECOMMENDED();
    }

    public function createOrUpdate(array $args): Version
    {
        $version = Version::firstOrNew();
        $version->fill($args);
        $version->save();

        return $version;
    }
}