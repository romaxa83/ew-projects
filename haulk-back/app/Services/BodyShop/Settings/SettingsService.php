<?php

namespace App\Services\BodyShop\Settings;

use App\Models\BodyShop\Settings\Settings;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

class SettingsService
{
    public function update(array $settings): Collection
    {
        $data = [];
        foreach ($settings as $name => $value) {
            if (in_array($name, Settings::JSON_FIELDS) && is_array($value)) {
                $value = json_encode($value);
            }

            $data[] = ['name' => $name, 'value' => $value];
        }

        Settings::upsert($data, ['name'], ['value']);

        return $this->getInfo();
    }

    public function getInfo(): Collection
    {
        return Settings::query()
            ->get()
            ->keyBy('name');
    }

    public function addAttachment(string $name, UploadedFile $file): void
    {
        try {
            $attachmentSetting = Settings::firstOrCreate(['name' => $name]);
            $attachmentSetting->addMediaWithRandomName($name, $file);
        } catch (Exception $e) {
            Log::error($e);
            throw $e;
        }
    }

    public function deleteAttachment(string $name): void
    {
        $attachmentSetting = Settings::firstOrCreate(['name' => $name]);
        $attachmentSetting->clearMediaCollection($name);
    }
}
