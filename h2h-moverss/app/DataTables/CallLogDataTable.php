<?php

namespace App\DataTables;

use App\Http\Controllers\CommunicationsController;
use App\Models\Client\Phone;
use App\Models\Division;
use App\Models\Employee\PbxData;
use App\Models\Zadarma\CallsEvents;
use App\Models\Zadarma\CallsEvents as Model;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder;

class CallLogDataTable extends DataTable
{

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @param Request $request
     * @return \Yajra\DataTables\DataTableAbstract
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function dataTable(Builder $query, Request $request): \Yajra\DataTables\DataTableAbstract
    {
        $projects_tz = Division::get(['id', 'miscs->tz as tz', 'miscs->zadarma_pbx_id as pbx_id']);
        $divisionData = Division::where('id', $request->session()->get('division.id'))
            ->first(['id', 'miscs->tz as tz', 'miscs->zadarma_pbx_id as pbx_id']);

        $user_tz = $divisionData->tz;
        $pbx_id = $divisionData->pbx_id;

        return datatables()
            ->eloquent($query
                ->where('pbx_id', $pbx_id)
//                ->whereRaw('`internal` != `destination`')
                ->where(function (Builder $query) {
                    $query->whereRaw('`internal` != `destination`')
                        ->whereRaw('IF((`event` = "NOTIFY_OUT_END" AND `disposition` = "answered"), `caller_id`, 1) != 0')
                        ->orWhere(function (Builder $query) {
                            $query->whereNull('internal')->whereNotNull('destination');
                        })
                        ->orWhere(function (Builder $query) {
                            $query->whereNull('destination')->whereNotNull('internal');
                        })
                        ->orWhere(function (Builder $query) {
                            $query->whereNull(['destination', 'internal']);
                        });
                })
                ->whereIn('event', ['NOTIFY_END', 'NOTIFY_OUT_END'])
                ->with(['internalPbxData.employee:id,pbx_ext,name,l_name'])
            )
            ->filter(function (Builder $query) {
                if ($this->request->has('search') && !empty($this->request->search['value'])) {
                    $query->where(function (Builder $query) {
                        $query->where('caller_id', 'like', '%' . $this->request->search['value'] . '%')
                            ->orWhere('destination', 'like', '%' . $this->request->search['value'] . '%')
                            ->orWhere('id', Phone::clearPhone($this->request->search['value']));
                    });
                }
//                foreach ($this->request->columns() as $index => $column) {
//                    if ($column['data'] === 'job_status' && !array_key_exists('search', $column)) {
//                        $query->where('active', 1);
//                    }
////                    if ($column['data'] === 'division_ids' && !array_key_exists('search', $column)) {
////                        $query->whereJsonContains('division_ids', 1);
////                    }
//                }
            })
            ->setRowId('id')
            ->orderColumns(['id', 'call_start'], '-:column $1')
            ->addColumn('event', function (CallsEvents $record) {
                $direction = str_contains($record->event, 'NOTIFY_OUT') ? 'out' : 'inc';

                if ($direction === 'out') {
                    if ($record->internal && !empty($record->internalPbxData) && !empty($record->internalPbxData->employee)) {
                        $src = $record->internalPbxData->employee->name . ' ' . $record->internalPbxData->employee->l_name;
                    } else {
                        $src = 'PBX: ' . $record->internal;
                    }

                    if (strlen($record->destination) < 6) {
                        // Local call
                        $record->load('destinationPbxData.employee:id,pbx_ext,name,l_name');

                        if (!empty($record->destinationPbxData) && !empty($record->destinationPbxData->employee)) {
                            $dst = $record->destinationPbxData->employee->name . ' ' . $record->destinationPbxData->employee->l_name;
                        } else {
                            $dst = 'PBX: ' . $record->destination;
                        }
                    } else {
                        $client = CommunicationsController::detectClient($record);
                        $dst = $client ?
                            "<a href='/orders?filter-client[]={$client->id}' target='_blank'>{$client->name} {$client->lname} ({$record->destination})</a>" :
                            $record->destination;
                    }
                } else {
                    $client = CommunicationsController::detectClient($record);
                    $src = $client ?
                        "<a href='/orders?filter-client[]={$client->id}' target='_blank'>{$client->name} {$client->lname} ({$record->caller_id})</a>" :
                        $record->caller_id;

                    if (!$record->destination && !$record->internal && $record->disposition === 'answered') {
                        $dst = 'Voice Mail';
                    } elseif (!empty($record->internalPbxData) && !empty($record->internalPbxData->employee) && $record->internal) {
                        $dst = $record->internalPbxData->employee->name.' '.$record->internalPbxData->employee->l_name;
                    } else {
                        $dst = 'n/a';
                    }
                }

                return '<i class="fas fa-long-arrow-' . ($direction === 'out' ? 'left' : 'right') .
                    '" title="' . ($direction === 'out' ? 'Outbound' : 'Inbound') . '"></i>' .
                    '<span class="ml-1 text-primary">' . $src . '</span> to <span class="text-primary">' . $dst . '</span>';
            })
            ->addColumn('call_start', function ($record) use ($projects_tz, $user_tz) {
                $date = Carbon::parse($record->call_start);
                $pbx_tz = $projects_tz->where('pbx_id', $record->pbx_id)->first()->tz;

                if ($pbx_tz !== $user_tz) {
                    $date = Carbon::parse($record->call_start, $pbx_tz)
                        ->tz($user_tz);
                }

                return $date->format('M j, Y \a\t g:i A');
            })
            ->addColumn('result', function ($record) {
                $is_answered = $record->disposition === 'answered';

                $duration = gmdate($record->duration > 60 * 60 ? 'H:i:s' : 'i:s', $record->duration);

                return '<div class="d-flex details-' . $record->id . '" data-call-id="' . $record->call_id_with_rec . '" data-pbx-call-id="' . $record->pbx_call_id . '">' .
                    '<div><span class="badge badge-' . ($is_answered ? 'success' : 'danger') . '">' . ucfirst($record->disposition) . '</span></div>' .
                    '<div class="ml-auto">' . ($record->call_id_with_rec ? '<button onclick="getCall(' . $record->id . ')" class="btn btn-outline-secondary waves-effect waves-themed px-1 py-0">Listen: ' . $duration . '</button>' : $duration) . '</div>' .
                    '</div>';
            })
            ->rawColumns(['event', 'result'])
            ->filterColumn('date_range', function (Builder $query, $keyword) {
                if ($keyword !== 'all') {
                    [$from, $to] = explode(',', $keyword);
                    $from .= ' 00:00:00';
                    $to .= ' 23:59:59';

                    $query->whereBetween('call_start', [$from, $to]);
                }
            })
            ->filterColumn('user', function (Builder $query, $keyword) use ($pbx_id) {
                if ($keyword !== 'all') {
                    $pbxData = PbxData::whereHas('employee', function ($q) use ($keyword) {
                        return $q->where('auth_user_id', $keyword);
                    })->where('pbx_id', $pbx_id)->first();
                    if ($pbxData) {
                        $query->where(function ($q) use ($pbxData) {
                            return $q->where('destination', $pbxData->pbx_ext)->where('pbx_id', $pbxData->pbx_id);
                        })->orWhere(function ($q) use ($pbxData) {
                            return $q->where('internal', $pbxData->pbx_ext)->where('pbx_id', $pbxData->pbx_id);
                        });
                    } else {
                        $query->where('internal', '-1');
                    }

                }
            });
//            ->filterColumn('division', function (Builder $query, $keyword) {
//                $pbx_ids = User::with('employee:id,pbx_ext,auth_user_id')
//                    ->whereJsonContains('division_ids', (int)$keyword)
//                    ->whereHas('employee', function (Builder $query) {
//                        $query->where('pbx_ext', '>', 0);
//                    })
//                    ->get(['id'])
//                    ->transform(function ($item) {
//                        return (int)$item->employee['pbx_ext'];
//                    })
//                    ->all();
//
//                $query->whereIn('destination', $pbx_ids)->orWhereIn('internal', $pbx_ids);
//            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Employee $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Model $model)
    {
        return $model
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
                'lengthMenu' => [
                    [25, 100, 500],
                    ['25 rows', '100 rows', '500 rows']
                ],
                'pageLength' => 25,
                'paging' => true,
                'ordering' => true,
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
            Column::make('call_start')->title('Call Date'),
            Column::make('event')->orderable(false),
            Column::make('result')->orderable(false),
            Column::make('user')->hidden(),
            Column::make('date_range')->hidden(),
            Column::make('division')->hidden(),
        ];
    }
}
