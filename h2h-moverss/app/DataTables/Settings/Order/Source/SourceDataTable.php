<?php

namespace App\DataTables\Settings\Order\Source;

use App\Models\Division;
use App\Models\Order\Source as Model;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class SourceDataTable extends DataTable
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
            ->whereJsonContains('division_ids', request()->session()->get('division.id'))
            ->with('divisions');

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
     * @throws \Exception
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('dt-table')
            ->parameters([
                'ordering' => true,
                'serverSide' => false,
                'searching' => true,
                'deferRender' => true,
                'lengthMenu' => [
                    [20, 50, -1],
                    ['20 rows', '50 rows', 'Show all']
                ],
                'pageLength' => -1,
                'columnDefs' => [
                    [
                        'targets' => [0],
                        'width' => '10%'
                    ],
//                    [
//                        'targets' => 'division_ids',
//                        'searchPanes' => [
//                            'show' => true
//                        ]
//                    ]
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
            ->editor(
                Editor::make()
                    ->fields([
                        Fields\Text::make('title'),
//                        Fields\Select::make('divisions')->data('division_ids')
//                            ->modelOptions((new Division()), 'title')->multiple(),
                        new \Yajra\DataTables\Html\Editor\Fields\Field([
                            'type' => 'colorpicker', 'name' => 'color', 'label' => 'color'
                        ]),
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
            Column::make('id')->searchable(false),
//            \App\Utils\ColumnDT::make('divisions')->searchable(false)->data('divisions')
//                ->title('Divisions')->addClass('editable')
//                ->renderJs('customRender()'),
            Column::make('color')->searchable(false)->addClass('editable')->renderJs('colorRender()'),
            Column::make('title')->addClass('editable'),
            Column::computed('action')->addClass('text-center'),
        ];
    }
}
