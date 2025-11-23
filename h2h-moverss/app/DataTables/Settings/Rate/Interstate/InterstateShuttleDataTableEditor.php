<?php

namespace App\DataTables\Settings\Rate\Interstate;

use App\Models\Calculation\IntrastateRatesWeight;
use App\Models\Settings\Interstate\ShuttlePrice;
use App\Models\Settings\Interstate\StateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;
use Yajra\DataTables\DataTablesEditorException;

class InterstateShuttleDataTableEditor extends DataTablesEditor
{
    protected $model = ShuttlePrice::class;

    protected function overlappingCheck($data, $ignoreModel)
    {
        if ($data['min'] >= $data['max']) {
            throw new DataTablesEditorException("From value need to be less than To value!");
        }
        $existingData = ($this->model)::where('division_id', $data['division_id'])
            ->when($ignoreModel->getKey(), function (Builder $q) use ($ignoreModel) {
                $q->where('id', '!=', $ignoreModel->getKey());
            })
            ->where(function (Builder $query) use ($data) {
                return $query
                    ->whereRaw('? between `min` and `max`', [$data['min']])
                    ->orWhereRaw('? between `min` and `max`', [$data['max']])
                    ->orWhere(function (Builder $q) use ($data) {
                        return $q->where('min', '>=', $data['min'])->where('max', '<=', $data['max']);
                    });
            })
            ->first();
        if ($existingData) {
            throw new DataTablesEditorException("Range overlapping with another range: From " . $existingData->min . " to " . $existingData->max . '!');
        }
        return false;
    }

    public function creating(Model $model, array $data)
    {
        $data['division_id'] = session('division')['id'];
        $this->overlappingCheck($data, $model);
        return $data;
    }


    public function updating(Model $model, array $data)
    {
        $data['division_id'] = session('division')['id'];
        $this->overlappingCheck($data, $model);
        return $data;
    }


    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules()
    {
        return [
            'min' => 'required|int',
            'max' => 'required|int',
            'price' => 'required|numeric|between:0,9999.99',
//            'email' => 'required|email|unique:' . $this->resolveModel()->getTable(),
//            'name'  => 'required',
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
            'min' => 'required|int',
            'max' => 'required|int',
            'price' => 'required|numeric|between:0,9999.99',
//            'email' => 'sometimes|required|email|' . Rule::unique($model->getTable())->ignore($model->getKey()),
//            'name'  => 'sometimes|required',
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
}
