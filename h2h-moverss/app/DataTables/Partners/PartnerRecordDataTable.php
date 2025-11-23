<?php

namespace App\DataTables\Partners;

use App\Models\Partners\Partner;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder;

class PartnerRecordDataTable extends DataTable
{

    /**
     * Build DataTable class.
     *
     * @param  mixed  $query  Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable(Builder $query)
    {
        return datatables()
            ->eloquent($query)
//            ->setRowId('id')
            ->addColumn('contact info', function (Partner $model) {

                $str = 'Contact person: <b>'. $model->contact_person .'</b><br>';
                $str .= 'Email: <b>'. $model->email .'</b><br>';
                $str .= 'Phone: <b>'. $model->phone .'</b><br>';

                return $str;
            })
            ->addColumn('trucks', function (Partner $model) {

                $str = null;
                foreach ($model->trucks as $truck) {
                    $str .= "<a href=\"".route('company.trucks.record', ['id' => $truck->id])."\"
                            class=\"btn btn-xs btn-primary waves-effect waves-themed mb-1\">
                            ". $truck->title ."
                       </a><br>";
                }

                return $str;
            })
            ->addColumn('employees', function (Partner $model) {

                $str = null;
                foreach ($model->employees as $employee) {
                    $str .= "<a href=\"".route('company.employees.record', ['id' => $employee->id])."\"
                            class=\"btn btn-xs btn-primary waves-effect waves-themed mb-1\">
                            ". $employee->full_name ."
                       </a><br>";
                }

                return $str;
            })
            ->addColumn('action', function (Partner $model) {
                return "<a href=\"".route('partner.show', ['id' => $model->id])."\"
                            class=\"btn btn-sm btn-primary waves-effect waves-themed editor-delete\">
                            View
                       </a>";
            })
            ->rawColumns(['action', 'contact info', 'trucks', 'employees']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param  Partner  $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Partner $model)
    {
        $divisionId = session()->get('division');

        return $model
            ->with(['trucks', 'employees'])
            ->where('division_id', $divisionId)
//            ->latest('id')
            ->newQuery();
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
                'paging' => false,
                'searching' => true,
            ])
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->editor(
                Editor::make()
                    ->fields([
                    ])
            );
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            Column::make('id')->orderable(true),
            Column::make('name')
                ->orderable(false),
            Column::make('contact info')
                ->orderable(false),
            Column::make('trucks')
                ->addClass('text-center')
                ->orderable(false),
            Column::make('employees')
                ->addClass('text-center')
                ->orderable(false),
            Column::computed('action')
                ->addClass('text-center')
                ->orderable(false),
        ];
    }
}

