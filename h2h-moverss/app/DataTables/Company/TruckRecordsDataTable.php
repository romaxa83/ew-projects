<?php

namespace App\DataTables\Company;

use App\Models\Truck\Truck;
use App\Models\Truck\Truck as Model;
use Illuminate\Database\Eloquent\Builder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Services\DataTable;

class TruckRecordsDataTable extends DataTable
{

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable(Builder $query)
    {
        $query
            ->whereJsonContains('division_ids', request()->session()->get('division.id'))
        ;

        return datatables()
            ->eloquent($query)
            ->filter(function (Builder $query) {
                foreach ($this->request->columns() as $index => $column) {
                    if ($column['data'] === 'work_status' && !array_key_exists('search', $column))
                        $query->where('active', 1);
                }

                if ($this->request->has('search') && !empty($this->request->search['value'])) {
                    $query->where(function (Builder $query) {
                        $query->orWhere('title', 'like', '%' . $this->request->search['value'] . '%')
                            ->orWhere('l_plate', 'like', '%' . $this->request->search['value'] . '%')
                            ->orWhere('nickname', 'like', '%' . $this->request->search['value'] . '%')
                            ->orWhere('vin', 'like', '%' . $this->request->search['value'] . '%');
                    });
                }

            })
            ->filterColumn('work_status', function ($query, $keyword) {
                if ($keyword !== 'all')
                    $query->where('active', $keyword);
            })
            ->setRowId('id')
            ->addColumn('division_ids',
                '{!! (in_array(1, $division_ids) ? "<span class=\'badge badge-secondary\'>Chicago, IL</span>": "").
                        (in_array(2, $division_ids) ? " <span class=\'badge badge-secondary\'>Los-Angeles, CA</span>": "") !!}')
            ->filterColumn('division_ids', function (Builder $query, $keyword) {
                if ($keyword !== 'all') {
                    $query->whereJsonContains('division_ids', [(int)$keyword]);
                }
            })
            ->addColumn('work_status',
                '{!! $active ? "<span class=\'badge badge-success\'>In work": "<span class=\'badge badge-danger\'>Sold" !!}</span>')
//            ->addColumn('action',
//                '<button type="button" class="btn btn-xs btn-success waves-effect waves-themed editor-delete">View</button>')
            ->addColumn('action', function ($record) {
                return "<a href=\"" . route('company.trucks.record', $record->id) . "\" class=\"btn btn-sm btn-primary waves-effect waves-themed editor-delete\">View</a>";
            })
            ->rawColumns(['work_status', 'action', 'division_ids']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param Truck $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Model $model)
    {
//        $query = $model->latest('active');
//
//        if(\Auth::user()->isPartner()){
//            /** @var $user User */
//            $user = \Auth::user();
//            if($user->employee->partner_id){
//                $query->where('partner_id', $user->employee->partner_id);
//            } else {
//                // здесь не нужно возвращать данные, костыльное решение
//                $query->where('created', '<', '1990-01-01');
//            }
//        }

        return $model->latest('active')->partnerTrucks()->newQuery();
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
            ])
            ->columns($this->getColumns())
            ->minifiedAjax()
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
    protected function getColumns()
    {
        return [
            Column::make('id'),
            Column::make('work_status'),
            Column::make('division_ids')->title('Divisions'),
            Column::make('title'),
            Column::make('nickname'),
            Column::make('l_plate')->title('License plate'),
            Column::make('vin')->title('VIN'),
            Column::make('year'),
//            Column::make('created_at'),
//            Column::make('updated_at'),
            Column::computed('action')->addClass('text-center'),
        ];
    }
}
