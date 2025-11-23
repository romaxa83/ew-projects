<?php

namespace App\DataTables\Settings\Order\StatusRoute;

use App\Models\Order\Status;
use App\Models\Order\StatusRoute as Model;
use App\Utils\ColumnDT;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class RecordsDataTable extends DataTable
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
            ->eloquent($query
                ->with(['status_from', 'status_to'])
                ->orderBy('status_id')
                ->orderBy('sort')
            )
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
//                'serverSide' => true,
                'searching' => true,
                'rowReorder' => [
                    'dataSrc' => 'sort',
                    'editor' => 'editor',
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
                    'editOnFocus' => true,
                ],
            ])
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->editor(
                Editor::make()
                    ->fields([
                        Fields\Text::make('sort'),
                        Fields\Select::make('status_from')
                            ->data('status_from.id')->modelOptions((new Status), 'title'),
                        Fields\Select::make('status_to')
                            ->data('status_to.id')->modelOptions((new Status), 'title'),
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
            Column::make('id')->searchable(),
            ColumnDT::make('status_from')
                ->data('status_from')
                ->title('From Status')
                ->addClass('editable')
                ->searchable(false)
                ->render([
                    '_' => 'id', 'display' => 'title',
                ]),
            ColumnDT::make('status_to')
                ->data('status_to')
                ->title('To Status')
                ->addClass('editable')
                ->searchable(false)
                ->render([
                    '_' => 'id', 'display' => 'title',
                ]),
            ColumnDT::make('status_from_')
                ->data('status_from.title')
                ->searchable()
                ->hidden(),
            ColumnDT::make('status_to_')
                ->data('status_to.title')
                ->searchable()
                ->hidden(),
            Column::computed('action')->addClass('text-center'),
        ];
    }
}
