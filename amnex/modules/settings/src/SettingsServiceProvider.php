<?php

declare(strict_types=1);

namespace Wezom\Settings;

use Wezom\Core\BaseServiceProvider;
use Wezom\Settings\Models\Setting;

class SettingsServiceProvider extends BaseServiceProvider
{
    protected array $morphMap = [
        Setting::class
    ];
    protected array $graphQlEnums = [];
    protected array $graphQlInputs = [];
}
