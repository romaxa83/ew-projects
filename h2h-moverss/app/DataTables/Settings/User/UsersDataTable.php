<?php

namespace App\DataTables\Settings\User;

use App\User;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UsersDataTable extends DataTable
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
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param  \App\User  $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(User $model)
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
            ->setTableId('users-table')
            ->parameters([
                'paging' => true,
                'searching' => true,
                'select' => true,
            ])
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom("<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'f><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'B>>".
                "<'row'<'col-sm-12'tr>>".
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>")
            ->orderBy(1)
            ->buttons(
                Button::make('create')->editor('editor')->className('btn-outline-secondary btn-sm'),
                Button::make('edit')->editor('editor')->className('btn-outline-secondary btn-sm'),
                Button::make('remove')->editor('editor')->className('btn-outline-secondary btn-sm'),
//                    Button::make('export')->className('btn-outline-secondary btn-sm'),
//                    Button::make('print')->className('btn-outline-secondary btn-sm'),
//                    Button::make('reset')->className('btn-outline-secondary btn-sm'),
//                    Button::make('reload')->className('btn-outline-secondary btn-sm')
            )
            ->editor(
                Editor::make()
                    ->fields([
                        Fields\Text::make('active'),
                        Fields\Text::make('name'),
                        Fields\Text::make('email'),
                        Fields\Password::make('password'),
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
            Column::make('id'),
            Column::make('active')->searchable(false)->renderJs('yesNoRender()'),
            Column::make('name'),
            Column::make('email'),
            Column::make('created_at'),
            Column::make('updated_at'),
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Users_'.date('YmdHis');
    }
}
