<?php

namespace App\DataTables\Settings\Item;

use App\Models\Item as Model;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ItemDataTable extends DataTable
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
                '<button type="button" class="btn btn-xs btn-danger waves-effect waves-themed editor-delete"><span class="fal fa-times"></span></button>');
    }

    /**
     * Get query source of dataTable.
     *
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
            ->setTableId('dt-table-items')
            ->parameters([
                'ordering' => true,
                'serverSide' => false,
                'searching' => true,
                'deferRender' => true,
                'lengthMenu' => [
                    [20, 30, 50, -1],
                    ['20 rows', '30 rows', '50 rows', 'Show all']
                ],
                'pageLength' => 20,
                'columnDefs' => [
                    [
                        'targets' => [0],
                        'width' => '10%'
                    ],
                    [
                        'targets' => 'item-group',
                        'searchPanes' => [
                            'show' => true
                        ]
                    ]
                ],
                'searchPanes' => [
                    'layout' => 'columns-1',
                    'clear' => false,
                    'columns' => [2],
//                        'dataLength' => 20
                ],
            ])
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->ajax([
                'url' => route('settings.items.records'),
            ])
            ->dom("<'row'<'col-lg-12 col-xl-3'P><'col-lg-12 col-xl-9'".
                "<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'f><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'B>>".
                "<'row'<'col-sm-12'<'table-responsive'tr>>>".
                "<'row'<'col-sm-12 col-md-5'l><'col-sm-12 col-md-7'p>>".">>")
            ->buttons(
                Button::make('create')->editor('editor')->className('btn-outline-secondary btn-sm'),
//                    Button::make('export')->className('btn-outline-secondary btn-sm'),
            )
            ->editor(
                Editor::make()
                    ->ajax(route('settings.items.records.editor'))
                    ->fields([
                        Fields\Text::make('title'),
                        Fields\Select::make('group')->data('group.id')->modelOptions((new \App\Models\Item\Group()),
                            'title'),
                        Fields\Text::make('weight'),
                        Fields\Text::make('cuft'),
                        Fields\Text::make('price'),
                    ]),
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
            Column::make('id'),
            Column::make('title')->addClass('editable'),
            \App\Utils\ColumnDT::make('group')->searchable(false)->data('group')->title('Group')->addClass('editable')->render([
                '_' => 'id', 'display' => 'title'
            ]),
            Column::make('weight')->title('Weight, lb')->addClass('editable'),
            Column::make('cuft')->title('Volume, cuft')->addClass('editable'),
            Column::make('price')->title('Price, $')->addClass('editable'),
            Column::computed('action')->addClass('text-center'),
        ];
    }
}
