<?php

namespace App\DataTables\Settings\Order\Source;

use App\Models\Order\Source;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\DataTablesEditor;

class SourceDataTableEditor extends DataTablesEditor
{

    protected $model = Source::class;

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules()
    {
        return [
            'title' => 'string|required|max:50',
            'color' => 'string|required|max:7',
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
            'color' => 'string|sometimes|required|max:7',
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
        $data['division_ids'][] = request()->session()->get('division.id');

        return $data;
    }

    /**
     * Добрасываем поля для апдейта.
     * @param  type  $instance
     * @param  array  $data
     * @return type
     */
    protected function updating($instance, $data)
    {
        $data['division_ids'][] = request()->session()->get('division.id');

        return $data;
    }

    /**
     * Подгружаем данные после сейва.
     * @param $instance
     * @return mixed
     */
    protected function saved($instance)
    {
        return $instance->load('divisions');
    }
}
