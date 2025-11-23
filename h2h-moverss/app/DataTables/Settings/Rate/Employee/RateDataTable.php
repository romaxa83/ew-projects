<?php

namespace App\DataTables\Settings\Rate\Employee;

use App\Models\Calculation\LocalHourlyRates as LocalHourlyRatesModel;
use App\Models\Division;
use App\Models\Employee;
use App\Models\Employee\Rate;
use App\Models\User\Role;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class RateDataTable extends DataTable
{

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        $query
            ->whereDivisionId(request()->session()->get('division.id'))
            ->with('division');

        return datatables()
            ->eloquent($query)
            ->addColumn('season_text', function ($record) {
                return ucfirst($record->season);
            })
            ->addColumn('division', function ($record) {
                return $record->division->title;
            })
            ->filterColumn('season_text', function ($query, $keyword) {
                if ($keyword != 'all')
                    $query->where('season', $keyword);
            })
            ->filterColumn('division', function ($query, $keyword) {
                if ($keyword != 'all')
                    $query->where('division_id', $keyword);
            })
            ->orderColumn('season_text', "season $1")
            ->orderColumn('division', "division_id $1")
            ->orderColumns(['workday', 'holiday', 'peakday'], "-:column $1")
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Rate $model)
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
//        dd('3');

        $divisions = Division::get()
            ->mapWithKeys(function($item) {
                return [$item['title'] => $item['id']];
            })
            ->toArray();

        $roles = Role::query()
            ->select(['id', 'title'])
            ->where('for_crew', true)
            ->get()
            ->pluck('id', 'title')
            ->toArray();

        $employees = Employee::query()
            ->get()
            ->mapWithKeys(function(Employee $item) {
                return [$item->full_name => $item['id']];
            })
            ->toArray()
        ;

        return $this->builder()
            ->setTableId('dt-table')
            ->parameters([
                'ordering' => true,
                'paging' => false,
                'searching' => true,
                'select' => 'single',
            ])
            ->columns($this->getColumns())
            ->minifiedAjax()->dom("<'row mb-3'<'col-sm-12 col-md-6 d-flex align-items-center justify-content-start'><'col-sm-12 col-md-6 d-flex align-items-center justify-content-end'B>>" .
                "<'row'<'col-sm-12'tr>>" .
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>")
            ->buttons(
                Button::make('create')->editor('editor')->className('btn-outline-secondary btn-sm'),
                Button::make('edit')->editor('editor')->className('btn-outline-secondary btn-sm'),
                Button::make('remove')->editor('editor')->className('btn-outline-secondary btn-sm')
            )
            ->editor(
                Editor::make()
                    ->fields([
                        Fields\Select::make('division_id', 'Division')
                            ->options($divisions),
                        Fields\Select::make('employee_id', 'Worker')
                            ->options($employees),
                        Fields\Select::make('role_id', 'Role')
                            ->options($roles),
                        Fields\Select::make('season', 'Season')
                            ->options([
                                'Winter' => LocalHourlyRatesModel::SEASON_WINTER,
                                'Summer' => LocalHourlyRatesModel::SEASON_SUMMER,
                            ]),
                        Fields\Number::make('workday', 'Workday, $'),
                        Fields\Number::make('holiday', 'Holiday, $'),
                        Fields\Number::make('peakday', 'Peakday, $'),
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
            Column::make('id')
                ->visible(false)
                ->searchable(false)
                ->orderable(false),
            Column::make('division')->title('Division'),
            Column::make('employee_name')->title('Worker'),
            Column::make('role_name')->title('Role'),
            Column::make('season_text')->addClass('editable')->title('Season'),
            Column::make('workday')->addClass('editable')->title('Workday, $'),
            Column::make('holiday')->addClass('editable')->title('Holiday, $'),
            Column::make('peakday')->addClass('editable')->title('Peakday, $'),
        ];
    }
}

