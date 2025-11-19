<?php

declare(strict_types=1);

namespace Wezom\Settings\Services;

use Wezom\Settings\Models\Setting;

final class SettingsService
{
    public function createOrUpdate(array $data = []): void
    {
        foreach ($data as $item) {
            if ($setting =  Setting::query()->where('key', $item['key'])->first()) {
                $setting->value = $item['value'];
            } else {
                $setting = new Setting();
                $setting->key = $item['key'];
                $setting->value = $item['value'];
            }
            $setting->save();
        }
    }
}
