<?php
namespace App\DataTables\Settings\Item;

use Illuminate\Database\Eloquent\Model;
use Yajra\DataTables\DataTablesEditor;

class GroupDataTableEditor extends DataTablesEditor
{

    protected $model = \App\Models\Item\Group::class;

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
     * @param Model $model
     * @return array
     */
    public function editRules(Model $model)
    {
        return [
            'title' => 'string|required|max:50',
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
