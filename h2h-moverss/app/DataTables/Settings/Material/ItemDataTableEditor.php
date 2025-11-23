<?php

namespace App\DataTables\Settings\Material;

use App\Models\Material;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\DataTablesEditor;

class ItemDataTableEditor extends DataTablesEditor
{

    protected $model = \App\Models\Material::class;

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules()
    {
        return [
            'title' => 'string|required|max:50',
            'group' => 'numeric|required|exists:extra_materials_types,id',
            'price' => 'numeric|nullable',
            'packing_price' => 'numeric|nullable',
            'unpacking_price' => 'numeric|nullable',
            'notes' => 'string|nullable|max:150',
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
            'group' => 'numeric|sometimes|exists:extra_materials_types,id',
            'price' => 'numeric|sometimes|nullable',
            'packing_price' => 'numeric|nullable',
            'unpacking_price' => 'numeric|nullable',
            'notes' => 'string|nullable|max:150',
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
        $max = Material::where([
                ['division_id', request()->session()->get('division.id')],
                ['group_id', $data['group']]
            ])
                ->max('sort') + 1;
        $data['sort'] = $max;

        return $this->parseInput($data);
    }

    /**
     * Добрасываем поля для апдейта.
     * @param  type  $instance
     * @param  array  $data
     * @return type
     */
    protected function updating($instance, $data)
    {
        return $this->parseInput($data);
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

    private function parseInput($data)
    {
        $data['need_packing'] = !empty($data['packing_price']);
        $data['need_unpacking'] = !empty($data['unpacking_price']);
        $data['notes'] = strip_tags($data['notes']);
        $data['division_id'] = request()->session()->get('division.id');

        return $data;
    }
}
