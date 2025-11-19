<?php

namespace Wezom\Quotes\Tests\Builders;

use Wezom\Core\Tests\Builders\BaseBuilder;
use Wezom\Settings\Models\Setting;

class SettingBuilder extends BaseBuilder
{
    public function modelClass(): string
    {
        return Setting::class;
    }
}
