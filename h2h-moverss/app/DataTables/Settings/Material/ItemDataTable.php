<?php

namespace App\DataTables\Settings\Material;

use App\Models\Material as Model;
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
        $query
            ->whereDivisionId(request()->session()->get('division.id'))
            ->with('group')
//            ->orderBy('group_id')
            ->orderBy('sort');

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
            ->setTableId('dt-table-items')
            ->parameters([
                'ordering' => false,
                'serverSide' => false,
                'searching' => true,
                'deferRender' => true,
                'pageLength' => -1,
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
                'rowReorder' => [
                    'selector' => 'tr>td.sorting',
                    'dataSrc' => 'sort',
                    'update' => false // this is key to prevent DT auto update
                ],
                'keys' => [
                    'columns' => ':not(:first-child)',
                    'keys' => [9],
                    'editOnFocus' => true
                ]
            ])
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->ajax([
                'url' => route('settings.materials.records'),
            ])
            ->dom("<'row'<'col-lg-12 col-xl-3'P><'col-lg-12 col-xl-9'".
                "<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'f><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'B>>".
                "<'row'<'col-sm-12'<'table-responsive'tr>>>".
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>".">>")
            ->buttons(
                Button::make('create')->editor('editor')->className('btn-outline-secondary btn-sm')
//                    Button::make('export')->className('btn-outline-secondary btn-sm'),
            )
            ->editor(
                Editor::make()
                    ->ajax(route('settings.materials.records.editor'))
                    ->fields([
                        Fields\Text::make('title'),
                        Fields\Select::make('group')->data('group.id')
                            ->modelOptions((new \App\Models\Material\Group()), 'title'),
                        Fields\Text::make('price'),
                        Fields\Text::make('packing_price'),
                        Fields\Text::make('unpacking_price'),
                        Fields\Text::make('notes'),
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
            Column::make('id')->orderable(false)->addClass('sorting'),
            Column::make('sort')->visible(false)->addClass('sorting'),
            Column::make('title')->addClass('editable'),
            \App\Utils\ColumnDT::make('group')->searchable(false)->data('group')->title('Group')
                ->addClass('editable')->render([
                    '_' => 'id', 'display' => 'title'
                ]),
            Column::make('price')->title('Price, $')->addClass('editable'),
            Column::make('packing_price')->title('Packing, $')->addClass('editable'),
            Column::make('unpacking_price')->title('Unpacking, $')->addClass('editable'),
            Column::make('notes')->title('Notes')->addClass('editable'),
            Column::computed('action')->addClass('text-center'),
        ];
    }
}
