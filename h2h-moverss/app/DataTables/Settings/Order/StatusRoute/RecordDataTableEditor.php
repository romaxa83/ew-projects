<?php

namespace App\DataTables\Settings\Order\StatusRoute;

use App\Models\Order\StatusRoute;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\DataTablesEditor;

class RecordDataTableEditor extends DataTablesEditor
{

    protected $model = StatusRoute::class;

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules()
    {
        return [];
    }

    /**
     * Get edit action validation rules.
     *
     * @param  Model  $model
     * @return array
     */
    public function editRules(Model $model)
    {
        return [];
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

    protected function creating($instance, $data): array
    {
        $data['sort'] = $instance->max('sort') + 1;

        return $data;
    }

    /**
     * Подгружаем данные после сейва.
     * @param $instance
     * @param $data
     * @return mixed
     */
    protected function saved($instance, $data)
    {
        if (isset($data['status_from'])) {
            return $instance->load([
                'status_from' => function ($q) use ($data) {
                    $q->where('id', $data['status_from']);
                },
                'status_to' => function ($q) use ($data) {
                    $q->where('id', $data['status_to']);
                },
            ]);
        }
        return $instance;
    }
}
