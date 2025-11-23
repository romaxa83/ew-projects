<?php

namespace App\DataTables\Company;

use App\Models\Employee as Model;
use App\Models\User\Role;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder;

class EmployeeRecordsDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param  mixed  $query  Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable(Builder $query)
    {
        $query
            ->whereJsonContains('division_ids', request()->session()->get('division.id'))
            ->with([
                'user.roles',
                'phones',
                'emails'
            ]);

        return datatables()
            ->eloquent($query)
            ->filter(function (Builder $query) {
                foreach ($this->request->columns() as $index => $column) {
                    if ($column['data'] === 'job_status' && !array_key_exists('search', $column)) {
                        $query->where('active', 1);
                    }
                }
                if ($this->request->has('search') && !empty($this->request->search['value'])) {
                    $query->where(function (Builder $query) {
                        $query->orWhere('name', 'like', '%'.$this->request->search['value'].'%')
                            ->orWhere('l_name', 'like', '%'.$this->request->search['value'].'%')
                            ->orWhereHas('user', function (Builder $query) {
                                return $query->where('name', 'like', '%'.$this->request->search['value'].'%');
                            })
                            ->orWhereHas('emails', function (Builder $query) {
                                return $query->where('value', 'like', '%'.$this->request->search['value'].'%');
                            })->orWhereHas('phones', function (Builder $query) {
                                return $query->where('value', 'like', '%'.$this->request->search['value'].'%');
                            });
                    });
                }
            })
            ->addColumn('job_status',
                '{!! $active ? "<span class=\'badge badge-success\'>In work": "<span class=\'badge badge-danger\'>Fired" !!}</span>')
            ->filterColumn('job_status', function ($query, $keyword) {
                if ($keyword !== 'all') {
                    $query->where('active', $keyword);
                }
            })
            ->setRowId('id')
            ->addColumn('primary_email', function ($record) {
                $email = $record->emails->first(function ($email, $key) {
                    return $email->is_primary == 1;
                });
                if (!$email) {
                    $email = $record->emails->first();
                }
                if ($email) {
                    return $email->value;
                }
                return '';
            })
            ->addColumn('auth_user', function ($record) {
                if ($record->user) {
                    return $record->user->name.' '.'['.$record->user->id.']';
                }
                return '';
            })
            ->addColumn('role', function ($record) {
                if ($record->user && $record->user->roles) {
                    return $record->user->roles->map(function ($role) {
                        return "<span class='badge badge-primary'>".$role->title."</span>";
                    })->implode(' ');
                }
                /**
                 * @var $role Role
                 */
//                return $record->user->roles->pluck('title'));
                return '';
            })
            ->addColumn('primary_phone', function ($record) {
                $phone = $record->phones->first(function ($phone, $key) {
                    return $phone->is_primary == 1;
                });
                if (!$phone) {
                    $phone = $record->phones->first();
                }
                if ($phone) {
                    return format_phone_number($phone->value, 7);
                }
                return '';
            })
            ->addColumn('division_ids',
                '{!! (is_array($division_ids) && in_array(1, $division_ids) ? "<span class=\'badge badge-secondary\'>Chicago, IL</span>": "").
                        (is_array($division_ids) && in_array(2, $division_ids) ? " <span class=\'badge badge-secondary\'>Los-Angeles, CA</span>": "") !!}')
            ->filterColumn('role', function (Builder $query, $keyword) {
                if ($keyword !== 'all') {
                    $query->whereHas('user.roles', function ($query) use ($keyword) {
                        $query->where('role_id', $keyword);
                    });
                    //$query->whereJsonContains('division_ids', [(int)$keyword]);
                }
            })

//            ->addColumn('job_status', function ($record) {
//                return $record->active;
//            })
            ->addColumn('action', function ($record) {
                return "<a href=\"".route('company.employees.record',
                        ['id' => $record->id])."\" class=\"btn btn-sm btn-primary waves-effect waves-themed editor-delete\">View</a>";
            })
            ->rawColumns(['job_status', 'action', 'division_ids', 'role']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param  \App\Models\Employee  $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Model $model)
    {
        return $model
            ->latest('active')
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
            Column::make('job_status')->title('Status')->orderable(false),
            Column::make('division_ids')->title('Divisions')->orderable(false),
            Column::make('name'),
            Column::make('l_name')->title('Last Name'),
            Column::make('role')->title('Roles')->orderable(false),
            Column::make('auth_user')->title('Auth user [id]'),
//            Column::make('user.roles')->title('Position'),
            Column::make('primary_email')->title('Primary email')->orderable(false),
            Column::make('primary_phone')->title('Primary phone')->orderable(false),
            Column::computed('action')->addClass('text-center')->orderable(false),
        ];
    }
}
