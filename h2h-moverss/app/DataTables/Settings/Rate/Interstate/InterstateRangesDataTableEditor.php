<?php

namespace App\DataTables\Settings\Rate\Interstate;

use App\Models\Calculation\IntrastateRatesWeight;
use App\Models\Settings\Interstate\StateRange;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;
use Yajra\DataTables\DataTablesEditorException;

class InterstateRangesDataTableEditor extends DataTablesEditor
{
    protected $model = StateRange::class;

    protected function overlappingCheck($data, $ignoreModel) {
        if($data['cbft_from'] >= $data['cbft_to'] ) {
            throw new DataTablesEditorException("From value need to be less than to value!");
        }
        $existingData = ($this->model)::where('division_id', $data['division_id'])
            ->when($ignoreModel->getKey(), function(Builder $q) use ($ignoreModel) {
                $q->where('id' , '!=', $ignoreModel->getKey());
            })
            ->where(function(Builder $query) use ($data) {
                return $query
                    ->whereRaw('? between `cbft_from` and `cbft_to`', [$data['cbft_from']])
                    ->orWhereRaw('? between `cbft_from` and `cbft_to`', [$data['cbft_to']])
                    ->orWhere(function(Builder $q) use ($data) {
                        return $q->where('cbft_from', '>=', $data['cbft_from'])->where('cbft_to', '<=', $data['cbft_to']);
                    });
            })
        ->first();
        if($existingData) {
            throw new DataTablesEditorException("Range overlapping with another range: From ".$existingData->cbft_from." to ".$existingData->cbft_to.'!');
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
            'cbft_from' => 'required|int',
            'cbft_to' => 'required|int',
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
            'cbft_from' => 'required|int',
            'cbft_to' => 'required|int',
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
