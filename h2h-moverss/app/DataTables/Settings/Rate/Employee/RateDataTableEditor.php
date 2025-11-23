<?php

namespace App\DataTables\Settings\Rate\Employee;

use App\Models\Division;
use App\Models\Employee;
use App\Models\Employee\Rate;
use App\Models\User\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;

class RateDataTableEditor extends DataTablesEditor
{
    protected $model = Rate::class;

    public function createRules()
    {
        return $this->rules(request('data.0'));
    }

    public function editRules(Model $model)
    {
        return $this->rules(current(request('data')), $model->id);
    }

    private function rules($data, $modelId = null) {
        return [
            'role_id' => [
                'required',
                Rule::exists(Role::TABLE, 'id'),
                Rule::unique(Rate::TABLE)
                    ->where(function ($query) use ($data, $modelId) {
                    return $query
                        ->where('employee_id', $data['employee_id'])
                        ->where('season', $data['season'])
                        ->where('division_id', $data['division_id']);
                })
                    ->ignore($modelId)
                ,
            ],
            'employee_id' => [
                'required',
                Rule::exists(Employee::TABLE, 'id'),
                Rule::unique(Rate::TABLE)
                    ->where(function ($query) use ($data, $modelId) {
                        return $query
                            ->where('role_id', $data['role_id'])
                            ->where('season', $data['season'])
                            ->where('division_id', $data['division_id']);
                    })
                    ->ignore($modelId)

            ],
            'division_id' => [
                'required',
                Rule::exists(Division::TABLE, 'id'),
                Rule::unique(Rate::TABLE)
                    ->where(function ($query) use ($data, $modelId) {
                        return $query
                            ->where('employee_id', $data['employee_id'])
                            ->where('season', $data['season'])
                            ->where('role_id', $data['role_id']);
                    })
                    ->ignore($modelId)

            ],
            'season' => [
                'required',
                'in:summer,winter',
                Rule::unique(Rate::TABLE)
                    ->where(function ($query) use ($data, $modelId) {
                        return $query
                            ->where('employee_id', $data['employee_id'])
                            ->where('role_id', $data['role_id'])
                            ->where('division_id', $data['division_id']);
                    })
                    ->ignore($modelId)
            ],
            'workday' => 'numeric|required|min:1',
            'holiday' => 'numeric|required|min:1',
            'peakday' => 'numeric|required|min:1',
        ];
    }



    public function removeRules(Model $model)
    {
        return [];
    }

    protected function saved($instance)
    {
        return $instance;
    }

    protected function creating($instance, $data)
    {
        return $this->parseInput($data, $instance->id);
    }

    protected function updating($instance, $data)
    {
        return $this->parseInput($data, $instance->id);
    }


    private function parseInput($data, $modelId = null)
    {
        $role = Role::find($data['role_id']);
        $employee = Employee::find($data['employee_id']);

        $data['division_id'] = Division::find($data['division_id'])->id;
        $data['employee_name'] = $employee->full_name;
        $data['role_name'] = $role->title;

        return $data;
    }
}
