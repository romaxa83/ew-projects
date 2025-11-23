<?php

namespace App\DataTables\Settings\Rate\Intrastate;

use App\Models\Calculation\IntrastateRates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;
use Yajra\DataTables\DataTablesEditorException;

class IntrastateRatesDataTableEditor extends DataTablesEditor
{
    protected $model = IntrastateRates::class;

//    protected function duplicatesCheck($data, $ignoreModel) {
//        $existingData = ($this->model)::where('division_id', $data['division_id'])
//            ->where('rate_distance_id', $data['rate_distance_id'])
//            ->where('rate_weight_id', $data['rate_weight_id'])
//            ->when($ignoreModel->getKey(), function(Builder $q) use ($ignoreModel) {
//                $q->where('id' , '!=', $ignoreModel->getKey());
//            })->first();
//        if($existingData) {
//            throw new DataTablesEditorException("Record with these ranges already exists!");
//        }
//
//    }
//
    public function creating(Model $model, array $data)
    {
        $data['division_id'] = session('division')['id'];
//        $this->duplicatesCheck($data, $model);
        return $data;
    }


    public function updating(Model $model, array $data)
    {
        $data['division_id'] = session('division')['id'];
//        $this->duplicatesCheck($data, $model);
        return $data;
    }


    public function saved(Model $model, array $data)
    {
        return $model->load(['weightRange:id,from,to', 'distanceRange:id,from,to']);
    }

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules()
    {
        $data = current(Request::get('data'));
//        dump($this->resolveModel()->getTable());
//        dump(session('division')['id']);
//        dump($data);
//        dd($this->resolveModel()->where(function ($q) use ($data) {
//            return $q->where('rate_distance_id', 'huy');
////                        ->where('rate_weight_id', $data['rate_weight_id'])
////                        ->where('division_id', session('division')['id']);
//        })->get());

        return [
            'coefficient' => 'required|numeric',
            'rate_distance_id' => [
                'required',
                Rule::unique($this->resolveModel()->getTable())->where(function ($q) use ($data) {
                    return $q->where('rate_distance_id', $data['rate_distance_id'])
                        ->where('rate_weight_id', $data['rate_weight_id'])
                        ->where('division_id', session('division')['id']);
                })
            ],
            'rate_weight_id' => [
                'required',
                Rule::unique($this->resolveModel()->getTable())->where(function ($q) use ($data) {
                    return $q->where('rate_distance_id', $data['rate_distance_id'])
                        ->where('rate_weight_id', $data['rate_weight_id'])
                        ->where('division_id', session('division')['id']);
                })
            ],
//            'rate_weight_id' => 'required',
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
        $data = current(Request::get('data'));

        return [
            'coefficient' => 'required|numeric',
            'rate_distance_id' => [
                'required',
                Rule::unique($model->getTable())->where(function ($q) use ($data) {
                    return $q->where('rate_distance_id', $data['rate_distance_id'])
                        ->where('rate_weight_id', $data['rate_weight_id'])
                        ->where('division_id', session('division')['id']);
                })->ignore($model->getKey()),
            ],
            'rate_weight_id' => [
                'required',
                Rule::unique($model->getTable())->where(function ($q) use ($data) {
                    return $q->where('rate_distance_id', $data['rate_distance_id'])
                        ->where('rate_weight_id', $data['rate_weight_id'])
                        ->where('division_id', session('division')['id']);
                })->ignore($model->getKey()),
            ],
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
