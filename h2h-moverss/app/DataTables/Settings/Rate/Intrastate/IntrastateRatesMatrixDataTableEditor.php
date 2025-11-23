<?php

namespace App\DataTables\Settings\Rate\Intrastate;

use App\Models\Calculation\IntrastateRates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\DataTablesEditorException;
use App\Http\Controllers\Settings\Rate\IntrastateController;

class IntrastateRatesMatrixDataTableEditor extends DataTablesEditor
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
//        dd($model);
//        dd($data);
//        $this->duplicatesCheck($data, $model);
        return $data;
    }


    public function saved(Model $model, array $data)
    {
        return $model->load(['weightRange:id,from,to', 'distanceRange:id,from,to']);
//        return IntrastateController::convert2Matrix(collect([$res]));
    }

    protected function toJson(array $data, array $errors = [], $error = '')
    {
        // need to load all for this milage
        if (!empty($data)) {
            $model = current($data);
            $res = IntrastateRates::with(['weightRange:id,from,to', 'distanceRange:id,from,to'])
                ->where('division_id', $model->division_id)->where('rate_distance_id', $model->rate_distance_id)->get();

            $data = array_values(IntrastateController::convert2Matrix($res));
        }
        if(!empty($errors)) {
            $d = current(Request::get('data'));
            foreach($errors as &$e) {
                $e['name'] = 'weights_range_'.$d['rate_weight_id'].'.'.$e['name'];
            }
        }
        return parent::toJson($data, $errors, $error);
    }


    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules()
    {
        $data = current(Request::get('data'));

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
//        return [];
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
