<?php

namespace App\DataTables\Settings\Order\StatusGroup;

use App\Models\Order\StatusGroup as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class StatusGroupDataTable extends DataTable
{

    /**
     * Build DataTable class.
     *
     * @param  mixed  $query  Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->setRowId('id')
            ->addColumn('action',
                '<button type="button" class="btn btn-xs btn-danger waves-effect waves-themed editor-delete"><span class="fal fa-times mr-1"></span> Delete</button>');
    }

    /**
     * Get query source of dataTable.
     *
     * @param  \App\Setting/Order/Status $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Model $model)
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('dt-table')
            ->parameters([
                'ordering' => false,
                'paging' => false,
                'searching' => true,
                'rowReorder' => [
                    'dataSrc' => 'order',
                    'editor' => 'editor'
                ],
                'columnDefs' => [
                    [
                        'targets' => [0],
                        'className' => 'reorder',
                    ],
                ],
                'keys' => [
                    'columns' => ':not(:first-child)',
                    'keys' => [9],
                    'editOnFocus' => true
                ]
            ])
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->editor(
                Editor::make()
                    ->fields([
                        Fields\Text::make('title'),
                        Fields\Text::make('sort'),
                        Fields\Text::make('in_report', 'Use in report'),
                        Fields\Text::make('in_funel_report', 'Use in funel report'),
                    ])
            );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('id')->searchable(false),
            Column::make('title')->addClass('editable'),
            Column::make('sort')->addClass('editable')->searchable(false),
            Column::make('in_report')->addClass('editable'),
            Column::make('in_funel_report')->addClass('editable'),
            Column::computed('action')->addClass('text-center'),
        ];
    }
}
