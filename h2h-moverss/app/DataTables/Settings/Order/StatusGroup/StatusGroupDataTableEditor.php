<?php

namespace App\DataTables\Settings\Order\StatusGroup;

use App\Models\Order\StatusGroup;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\DataTablesEditor;

class StatusGroupDataTableEditor extends DataTablesEditor
{

    protected $model = StatusGroup::class;

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules()
    {
        return [
            'title' => 'string|required|max:50',
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
            'title' => 'string|sometimes|required|max:50',
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

    /**
     * Добрасываем поля для создания.
     * @param  type  $instance
     * @param  array  $data
     * @return type
     */
    protected function creating($instance, $data)
    {
        $data['sort'] = $instance->max('sort') + 1;

        if(is_null($data['in_funel_report'])) {
            $data['in_funel_report'] = 0;
        }

        return $data;
    }
}
