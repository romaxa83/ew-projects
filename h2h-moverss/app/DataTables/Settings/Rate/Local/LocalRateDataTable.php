<?php

namespace App\DataTables\Settings\Rate\Local;

use App\Models\Calculation\LocalHourlyRates as Model;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class LocalRateDataTable extends DataTable
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
            ->orderColumns(['crew_qty', 'workday', 'holiday', 'peakday'], "-:column $1")
//            ->rawColumns(['division_id'])
            ->setRowId('id');
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
            ->setTableId('dt-table')
            ->parameters([
                'ordering' => true,
                'paging' => false,
                'searching' => true,
                'select' => true,
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
                        Fields\Text::make('crew_qty', 'Crew, qty'),
                        Fields\Select::make('season', 'Season')
                            ->options(['Winter' => 'winter', 'Summer' => 'summer']),
                        Fields\Text::make('workday', 'Workday, $'),
                        Fields\Text::make('holiday', 'Holiday, $'),
                        Fields\Text::make('peakday', 'Peakday, $'),
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
            Column::make('id')->visible(false)->searchable(false)->orderable(false),
            Column::make('crew_qty')->title('Crew, qty')->addClass('editable')->orderable(true),
            Column::make('division')->title('Division'),
            Column::make('season_text')->addClass('editable')->title('Season'),
            Column::make('workday')->addClass('editable')->title('Workday, $'),
            Column::make('holiday')->addClass('editable')->title('Holiday, $'),
            Column::make('peakday')->addClass('editable')->title('Peakday, $'),
        ];
    }
}
