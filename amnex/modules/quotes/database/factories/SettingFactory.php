<?php

namespace Wezom\Quotes\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Wezom\Settings\Models\Setting;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    protected $model = Setting::class;

    public function definition(): array
    {
        return [
            'key' => Setting::KEY_RATE_0_20_MILES,
            'value' => 100,
            'title' => null,
            'group_title' => null,
            'type' => 'int',
        ];
    }
}
