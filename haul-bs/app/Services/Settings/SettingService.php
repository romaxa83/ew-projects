<?php

namespace App\Services\Settings;

use App\Models\Settings\Settings;
use Illuminate\Http\UploadedFile;

class SettingService
{
    public function __construct()
    {}

    public function update(array $settings): void
    {
        $data = [];
        foreach ($settings as $name => $value) {
            if (in_array($name, Settings::JSON_FIELDS) && is_array($value)) {
                $value = json_encode($value);
            }

            $data[] = ['name' => $name, 'value' => $value];
        }

        Settings::upsert($data, ['name'], ['value']);
    }

    public function uploadLogo(UploadedFile $file, string $name = Settings::LOGO_FIELD): void
    {
        $model = Settings::firstOrCreate(['name' => $name]);
        $model->addMediaWithRandomName($name, $file);
    }

    public function deleteLogo(string $name = Settings::LOGO_FIELD): void
    {
        $model = Settings::firstOrCreate(['name' => $name]);
        $model->clearMediaCollection($name);
    }
}
