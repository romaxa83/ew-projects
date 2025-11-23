<?php

namespace App\DataTables\Settings\Item;

use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\DataTablesEditor;

class ItemDataTableEditor extends DataTablesEditor
{

    protected $model = \App\Models\Item::class;

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules()
    {
        return [
            'title' => 'string|required|max:250',
            'group' => 'numeric|required|exists:items_groups,id',
            'weight' => 'numeric|nullable',
            'cuft' => 'numeric|nullable',
            'price' => 'numeric|nullable',
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
            'title' => 'string|sometimes|required|max:250',
            'group' => 'numeric|sometimes|exists:items_groups,id',
            'weight' => 'numeric|sometimes|nullable',
            'cuft' => 'numeric|sometimes|nullable',
            'price' => 'numeric|sometimes|nullable',
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
     * Подгружаем данные после сейва.
     * @param $instance
     * @param $data
     * @return mixed
     */
    protected function saved($instance, $data)
    {
        return $instance->load([
            'group' => function ($q) use ($data) {
                $q->where('id', $data['group']);
            }
        ]);
    }
}
