<?php

namespace App\DataTables\Settings\Rate\Intrastate;

use App\Models\Calculation\IntrastateRatesDistance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;
use Yajra\DataTables\DataTablesEditorException;

class IntrastateRatesDistanceDataTableEditor extends DataTablesEditor
{
    protected $model = IntrastateRatesDistance::class;

    protected function overlappingCheck($data, $ignoreModel) {
        if($data['from'] >= $data['to'] ) {
            throw new DataTablesEditorException("From value need to be less than to value!");
        }
        $existingData = ($this->model)::where('division_id', $data['division_id'])
            ->when($ignoreModel->getKey(), function(Builder $q) use ($ignoreModel) {
                $q->where('id' , '!=', $ignoreModel->getKey());
            })
            ->where(function(Builder $query) use ($data) {
                return $query
                    ->whereRaw('? between `from` and `to`', [$data['from']])
                    ->orWhereRaw('? between `from` and `to`', [$data['to']])
                    ->orWhere(function(Builder $q) use ($data) {
                        return $q->where('from', '>=', $data['from'])->where('to', '<=', $data['to']);
                    });
            })
        ->first();
        if($existingData) {
            throw new DataTablesEditorException("Range overlapping with another range: From ".$existingData->from." to ".$existingData->to.'!');
        }
        return false;
    }

    public function creating(Model $model, array $data) {
        $data['division_id'] = session('division')['id'];
        $this->overlappingCheck($data, $model);
        return $data;
    }


    public function updating(Model $model, array $data) {
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
            'from' => 'required|int',
            'to' => 'required|int',
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
            'from' => 'required|int',
            'to' => 'required|int',
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
