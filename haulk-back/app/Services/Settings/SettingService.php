<?php

namespace App\Services\Settings;

use App\Models\Settings\Setting;
use DB;
use Exception;
use Log;
use Throwable;

class SettingService
{
    /**
     * @param array $attributes
     * @param string $group
     *
     * @return array
     * @throws Throwable
     */
    public function update(array $attributes, string $group): array
    {
        DB::beginTransaction();
        try {
            foreach ($attributes as $alias => $value) {
                $setting = Setting::whereAlias($alias)->whereGroup($group)->first();
                if (!$setting) {
                    $setting = Setting::create(
                        [
                            'alias' => $alias,
                            'group' => $group,
                        ]
                    );
                }
                $setting->value = $value;
                $setting->save();
            }
            DB::commit();
            return $attributes;
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
            throw $exception;
        }
    }

    public function findByGroup(string $name): array
    {
        $result = [];

        foreach (Setting::whereGroup($name)->get() as $value) {
            $result[$value->alias] = $value->value;
        }

        return $result;
    }
}
