<?php

namespace App\DataTables\Settings\Rate\Local;


use App\Models\Calculation\LocalHourlyRates;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;

class LocalRateDataTableEditor extends DataTablesEditor
{

    protected $model = LocalHourlyRates::class;

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules()
    {
        return [
//            'crew_qty' => 'numeric|required|max:50|unique:local_hourly_rates,crew_qty',
            'crew_qty' => [
                'numeric',
                'required',
                'max:50',
                Rule::unique('local_hourly_rates')->where(function ($query) {
                    return $query->where('crew_qty', $this->currentData['crew_qty'])
                        ->where('division_id', request()->session()->get('division.id'))
                        ->where('season', $this->currentData['season']);
                }),
            ],
            'season' => 'required|in:summer,winter',
            'workday' => 'numeric|required|min:1',
            'holiday' => 'numeric|required|min:1',
            'peakday' => 'numeric|required|min:1',
        ];
    }

    /**
     * Get edit action validation rules.
     *
     * @param Model $model
     * @return array
     */
    public function editRules(Model $model)
    {
        return [
            'crew_qty' => [
                'numeric',
                'required',
                'max:50',
                Rule::unique('local_hourly_rates')->where(function ($query) {
                    return $query->where('crew_qty', $this->currentData['crew_qty'])
                        ->where('division_id', request()->session()->get('division.id'))
                        ->where('season', $this->currentData['season']);
                })->ignore($model->id),
            ],
            'season' => 'required|in:summer,winter',
            'workday' => 'numeric|required|min:1',
            'holiday' => 'numeric|required|min:1',
            'peakday' => 'numeric|required|min:1',
        ];
    }

    /**
     * Get remove action validation rules.
     *
     * @param Model $model
     * @return array
     */
    public function removeRules(Model $model)
    {
        return [];
    }

    /**
     * Подгружаем данные после сейва.
     * @param $instance
     * @return mixed
     */
    protected function saved($instance)
    {
        return $instance->load('division');
    }

    /**
     * Добрасываем поля для создания.
     * @param  type  $instance
     * @param  array  $data
     * @return type
     */
    protected function creating($instance, $data)
    {
        return $this->parseInput($data);
    }

    /**
     * Добрасываем поля для апдейта.
     * @param  type  $instance
     * @param  array  $data
     * @return type
     */
    protected function updating($instance, $data)
    {
        return $this->parseInput($data);
    }

    private function parseInput($data)
    {
        $data['division_id'] = request()->session()->get('division.id');

        return $data;
    }
}
