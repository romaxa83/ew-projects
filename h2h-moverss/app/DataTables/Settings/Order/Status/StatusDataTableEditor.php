<?php

namespace App\DataTables\Settings\Order\Status;

use App\Models\Order;
use App\Models\Order\Status;
use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\DataTablesEditor;

class StatusDataTableEditor extends DataTablesEditor
{

    protected $model = Status::class;

    /**
     * Get create action validation rules.
     *
     * @return array
     */
    public function createRules(): array
    {
        return [
            'title' => 'string|required|max:50',
            'color' => 'string|required|max:7',
            'enable_dispatch' => 'prohibits:disable_dispatch,true',
            'disable_dispatch' => 'prohibits:enable_dispatch,true',
        ];
    }

    /**
     * Get edit action validation rules.
     *
     * @param  Model  $model
     * @return array
     */
    public function editRules(Model $model): array
    {
        return [
            'title' => 'string|sometimes|required|max:50',
            'color' => 'string|sometimes|required|max:7',
            'enable_dispatch' => 'prohibits:disable_dispatch,true',
            'disable_dispatch' => 'prohibits:enable_dispatch,true',
        ];
    }

    public function attributes(): array
    {
        return [
            'enable_dispatch' => 'Enable services to dispatch',
            'disable_dispatch' => 'Remove services from dispatch',
        ];
    }

    public function messages(): array
    {
        return [
            'prohibits' => 'Other service must be unchecked',
        ];
    }

    /**
     * Get remove action validation rules.
     *
     * @param  Model  $model
     * @return array
     */
    public function removeRules(Model $model): array
    {
        return [];
    }

    /**
     * Добрасываем поля для создания.
     * @param   $instance
     * @param  array  $data
     * @return array
     */
    protected function creating($instance, $data): array
    {
        $data['sort'] = $instance->max('sort') + 1;

        return $this->parseInput($data, $instance);
    }

    protected function deleting(Model $model, array $data)
    {
        $total = Order::whereStatusId($data['id'])->count();

        if ($total) {
            throw new \Yajra\DataTables\DataTablesEditorException('This status used in '.$total.' orders. You need to change this status in the orders, then you can delete it');
        }

        Order\StatusRoute::query()
            ->where('status_id', $data['id'])
            ->orWhere('route_to_status_id', $data['id'])
            ->delete();
    }

    protected function updating($instance, $data): array
    {
        return $this->parseInput($data, $instance);
    }

    private function parseInput($data, $instance): array
    {
        $actions = (array) $instance->actions;


        $enabled = isset($data['enable_dispatch'][0]) && $data['enable_dispatch'][0];
        if ($enabled && !in_array('enable_dispatch', $actions, true)) {
            $actions[] = 'enable_dispatch';
        } elseif (!$enabled && ($key = array_search('enable_dispatch', $actions, true)) !== false) {
            unset($actions[$key]);
        }

        $enabled = isset($data['disable_dispatch'][0]) && $data['disable_dispatch'][0];
        if ($enabled && !in_array('disable_dispatch', $actions, true)) {
            $actions[] = 'disable_dispatch';
        } elseif (!$enabled && ($key = array_search('disable_dispatch', $actions, true)) !== false) {
            unset($actions[$key]);
        }

        $data['actions'] = $actions;

        return $data;
    }
}
