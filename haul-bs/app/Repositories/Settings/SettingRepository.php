<?php

namespace App\Repositories\Settings;

use App\Foundations\Modules\Location\Models\State;
use App\Foundations\Repositories\BaseEloquentRepository;
use App\Models\Settings\Settings;
use Illuminate\Database\Eloquent\Collection;

final readonly class SettingRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Settings::class;
    }

    public function getInfo(): Collection
    {
        return Settings::query()
            ->get()
            ->keyBy('name');
    }

    public function getInfoForEcomm(): Collection
    {
        $data = $this->getInfo();

        foreach ($data as $name => $value) {
            if($name == 'state_id'){
                $state = State::find($value->value);
                $data->put('state_name', (object)['value' => $state->name]);
            }
            if($name == 'ecommerce_state_id'){
                $state = State::find($value->value);
                $data->put('ecommerce_state_name', (object)['value' => $state->name]);
            }
        }

        return $data;
    }
}
