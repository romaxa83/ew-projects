<?php

namespace App\Services\Settings;

use App\Dto\Settings\SettingsDto;
use App\Models\Settings\Settings;

class SettingsService
{
    public function update(SettingsDto $dto): Settings
    {
        return $this->editSettings($dto, $this->getSettingsItem());
    }

    public function getSettingsItem(): Settings
    {
        return Settings::firstOrCreate([]);
    }

    private function editSettings(SettingsDto $dto, Settings $settings): Settings
    {
        $settings->email = $dto->getEmail();
        $settings->phone = $dto->getPhone();
        $settings->save();

        return $settings->refresh();
    }

    public function show(): Settings
    {
        return $this->getSettingsItem();
    }
}
