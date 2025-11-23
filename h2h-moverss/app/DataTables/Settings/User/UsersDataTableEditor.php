<?php

namespace App\DataTables\Settings\User;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Yajra\DataTables\DataTablesEditor;
use Hash;

class UsersDataTableEditor extends DataTablesEditor
{
    protected $model = User::class;

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules()
    {
        return [
            'email' => 'required|email|unique:'.$this->resolveModel()->getTable(),
            'name' => 'required',
        ];
    }

    /**
     * Get edit action validation rules.
     *
     * @param  Model  $model
     * @return array
     */
    public function editRules(Model $model)
    {
        return [
            'email' => 'sometimes|required|email|'.Rule::unique($model->getTable())->ignore($model->getKey()),
            'name' => 'sometimes|required',
        ];
    }

    /**
     * Get remove action validation rules.
     *
     * @param  Model  $model
     * @return array
     */
    public function removeRules(Model $model)
    {
        return [];
    }

    public function updating(Model $model, array $data)
    {
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return $data;
    }

    protected function creating($instance, $data)
    {
        $data['password'] = Hash::make($data['password']);

        return $data;
    }
}
