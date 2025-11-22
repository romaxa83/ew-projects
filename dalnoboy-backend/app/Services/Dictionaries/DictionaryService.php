<?php

namespace App\Services\Dictionaries;

use App\Entities\Dictionaries\DictionaryItem;
use App\Enums\Dictionaries\DictionaryEnum;
use App\Models\BaseModel;
use Illuminate\Support\Facades\DB;

class DictionaryService
{
    public function getList(): array
    {
        $settings = DictionaryEnum::getConfig();
        $list = [];
        foreach ($settings as $key => $setting) {
            /** @var BaseModel $class */
            $class = $setting['class'];

            $res = $class::query();
            if ($setting['active'] ?? true) {
                $res->active();
            }
            $res->selectRaw('count(*) as cnt');
            if ($setting['updated'] ?? true) {
                $res->addSelect(DB::raw('max(updated_at) as updated_at'));
            }

            $data = $res->first();

            $list[] = new DictionaryItem(
                $key,
                $data->cnt ?? 0,
                $data->updated_at ?? null
            );
        }

        return $list;
    }
}
