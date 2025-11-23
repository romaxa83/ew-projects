<?php

namespace App\DataTables\Settings\Order\Status;

use App\Models\Order\Status as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class StatusDataTable extends DataTable
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
            ->eloquent($query->selected())
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
                ]
            ])
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->editor(
                Editor::make()
                    ->fields([
                        Fields\Text::make('title'),
                        new \Yajra\DataTables\Html\Editor\Fields\Field([
                            'type' => 'colorpicker', 'name' => 'color', 'label' => 'color'
                        ]),
                        Fields\Text::make('order'),
                        Fields\Checkbox::make('enable_dispatch')->options(['Active' => true]),
                        Fields\Checkbox::make('disable_dispatch')->options(['Active' => true]),
                        Fields\Select::make('group')->data('group.id')->modelOptions((new \App\Models\Order\StatusGroup),
                            'title'),
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
            Column::make('color')->searchable(false)->addClass('editable')->renderJs('colorRender()'),
            Column::make('title')->addClass('editable'),
            \App\Utils\ColumnDT::make('group')->searchable(false)->data('group')->title('Group')->addClass('editable')->render([
                '_' => 'id', 'display' => 'title'
            ]),
            Column::make('enable_dispatch')->title('Enable services for dispatch')->addClass('editable')->renderJs('yesNoRender()'),
            Column::make('disable_dispatch')->title('Remove services from dispatch')->addClass('editable')->renderJs('yesNoRender()'),
//            Column::make('is_final')->title('Marked as closed order')->addClass('editable')->renderJs('yesNoRender()'), // Koza
            Column::computed('action')->addClass('text-center'),
        ];
    }
}
