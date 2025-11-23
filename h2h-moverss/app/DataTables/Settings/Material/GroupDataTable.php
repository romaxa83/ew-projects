<?php
namespace App\DataTables\Settings\Material;

use App\Models\Material\Group AS Model;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class GroupDataTable extends DataTable
{

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
                ->eloquent($query)
                ->setRowId('id')
                ->addColumn('action', '<button type="button" class="btn btn-xs btn-danger waves-effect waves-themed editor-delete"><span class="fal fa-times mr-1"></span> Delete</button>');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Setting/Order/Status $model
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
                ->setTableId('dt-table-group')
                ->parameters([
                    'ordering' => true,
                    'paging' => true,
                    'searching' => true,
                    'pageLength' => 5
                ])
                ->columns($this->getColumns())
                ->minifiedAjax()
                ->ajax([
                    'url' => route('settings.materials.group'),
                ])
                ->dom("<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'f><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'B>>" .
                    "<'row'<'col-sm-12'tr>>" .
                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>")
                ->buttons(
                    Button::make('create')->editor('editor')->className('btn-outline-secondary btn-sm'),
//                    Button::make('export')->className('btn-outline-secondary btn-sm'),
                )
                ->editor(
                    Editor::make()
                    ->ajax(route('settings.materials.group.editor'))
                    ->fields([
                        Fields\Text::make('title'),
                    ])
                )
        ;
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
            Column::computed('action')->addClass('text-center'),
        ];
    }
}
