<?php

namespace App\DataTables\Settings\Rate\Interstate;

use App\Http\Controllers\Settings\Rate\InterstateController;
use App\Models\Calculation\IntrastateRates;
use App\Models\Settings\Interstate\StateCoefficient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;
use Yajra\DataTables\Facades\DataTables;
use Yajra\DataTables\DataTablesEditorException;
use Cdtweb\UsStatesList;

class InterstateRatesMatrixDataTableEditor extends DataTablesEditor
{
    protected $model = StateCoefficient::class;

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


//    public function saved(Model $model, array $data)
//    {
//        return $model->load(['weightRange:id,from,to', 'distanceRange:id,from,to']);
////        return IntrastateController::convert2Matrix(collect([$res]));
//    }

    protected function toJson(array $data, array $errors = [], $error = '')
    {
        // need to load all for this milage
        if (!empty($data)) {
            $model = current($data);
//            $matrix = self::getBlankMatrix($model->range_id);
            $matrix = array_intersect_key(InterstateController::getBlankMatrix($model->range_id), array_flip([$model->from_state]));
            $res = StateCoefficient::where('division_id', $model->division_id)->where('range_id', $model->range_id)
                ->where('from_state', $model->from_state)->get();
            foreach ($res as $record) {
                $matrix[$record->from_state]['to_' . $record->to_state]['id'] = $record->id;
                $matrix[$record->from_state]['to_' . $record->to_state]['price'] = $record->price;
            }
            $data = array_values($matrix);
        }
        if (!empty($errors)) {
            $d = current(Request::get('data'));
            foreach ($errors as &$e) {
                $e['name'] = 'to_' . $d['to_state'] . '.' . $e['name'];
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
            'price' => 'nullable|numeric',
            'from_state' => Rule::in(UsStatesList::abbreviations()),
            'to_state' => Rule::in(UsStatesList::abbreviations()),
            'range_id' => [
                'exists:App\Models\Settings\Interstate\StateRange,id',

            ],
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
            'price' => 'nullable|numeric',
            'from_state' => Rule::in(UsStatesList::abbreviations()),
            'to_state' => Rule::in(UsStatesList::abbreviations()),
            'range_id' => [
                'exists:App\Models\Settings\Interstate\StateRange,id',
                Rule::unique($model->getTable())->where(function ($q) use ($data) {
                    return $q->where('range_id', $data['range_id'])
                        ->where('from_state', $data['from_state'])
                        ->where('to_state', $data['to_state'])
                        ->where('division_id', session('division')['id']);
                })->ignore($model->getKey()),
            ],
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
