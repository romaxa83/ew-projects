<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Order;
use App\Models\Order\Payment;
use App\Models\Order\Status;
use App\Models\Order\StatusChangeHistory;
use App\Models\Order\StatusGroup;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use DateInterval, DatePeriod;

class ByManagersReportController extends Controller
{
    /**
     * @var array
     */
    private $rangeDates;
    private $source2name;
    private $managers;

    /**
     * Загрузка данных для отчета.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function report(Request $request): JsonResponse
    {
        $this->managers = User::whereActive(1)->get(['id', 'name'])->keyBy('id');

        $this->rangeDates = [];
        $date_start = Carbon::parse($request->date_start);
        $date_end = Carbon::parse($request->date_end)->addDay();
        $range = new DatePeriod($date_start, new DateInterval('P1D'), $date_end);
        foreach ($range as $dt) {
            $this->rangeDates[] = $dt->format('Y-m-d');
        }

        $report = [];
        if ($request->selectBy === 'users') {
            $report = $this->reportByManager();
        } elseif ($request->selectBy === 'branch') {
            $report = $this->reportByBranch();
        }


        return response()
            ->json([
                'success' => true,
                'report' => $report,
            ]);
    }

    /**
     * Отчет по менеджерам.
     * @return array
     */
    private function reportByManager(): array
    {
        $headerRecords = [
            [
                'value' => 'By Manager',
                'class' => 'fs-xl',
            ]
        ];
        foreach ($this->rangeDates as $dt) {
            $headerRecords [] = [
                'value' => date('m/d', strtotime($dt)),
                'class' => 'fs-sm',
            ];
        }
        $records = $this->reportByManagerRecords();

        return [
            'header' => [
                'colspan' => 3,
                'records' => $headerRecords
            ],
            'records' => $records,
        ];
    }

    private function reportByManagerRecords(): array
    {
        $headerRecords = [];
        foreach ($this->rangeDates as $dt) {
            $weekDayName = date('D', strtotime($dt));

            $headerRecords [] = [
                'value' => $weekDayName,
                'class' => 'weekdays week-'.$weekDayName,
            ];
        }
        $headerRecords [] = [
            'value' => 'Act',
            'class' => 'fw-700',
        ];
        $headerRecords [] = [
            'value' => 'Plan',
            'class' => 'text-muted fw-700',
        ];
        $headerRecords [] = [
            'value' => 'Act %',
            'class' => 'text-muted fw-700',
        ];

        $report = [];
        foreach ($this->managers as $manager) {

            $records = [];
            $records = $this->byManagerCustomReports($records, $manager->id);
            $records = $this->byManagerStatusGroupReports($records, $manager->id);

            $v = [
                'title' => $manager->name,
                'expanded' => false,
                'header' => $headerRecords,
                'records' => $records,
                'hasRecords' => (bool) count($records),
            ];

            $report[] = $v;
        }

        return $report;
    }

    private function byManagerCustomReports($records, $user_id)
    {
        $rows = [];
        $total = 0;
        foreach ($this->rangeDates as $date) {
            $res = StatusChangeHistory::whereBetween('created_at', ["{$date} 00:00:00", "{$date} 23:59:59"])
                ->whereUserId($user_id)->where('new_status', 1)->count();
            $total += $res;
            $rows[$date] = [
                'value' => $res,
            ];
        }
        $rows['act'] = [
            'value' => $total,
            'class' => 'fw-700',
        ];
        $rows['plan'] = [
            'value' => 77,
            'class' => 'text-muted fw-700',
        ];
        $rows['act_per'] = [
            'value' => number_format(($total / $rows['plan']['value'] * 100), 2, '.', ''),
            'class' => 'text-muted fw-700',
        ];

        $records[] = [
            'title' => 'New Leads',
            'rows' => $rows
        ];


        $rows = [];
        $total = 0;
        foreach ($this->rangeDates as $date) {
            $payments = Payment::whereBetween('created_at', ["{$date} 00:00:00", "{$date} 23:59:59"])
                ->get(['order_id', 'amount']);

            $res = 0;
            foreach ($payments as $payment) {
                if (Order::whereId($payment->order_id)->whereUserId($user_id)->exists()) {
                    $res += $payment->amount;
                }
            }
            $total += $res;
            $rows[$date] = [
                'value' => $res,
            ];
        }
        $rows['act'] = [
            'value' => $total,
            'class' => 'fw-700',
        ];
        $rows['plan'] = [
            'value' => 100,
            'class' => 'text-muted fw-700',
        ];
        $rows['act_per'] = [
            'value' => number_format(($total / $rows['plan']['value'] * 100), 2, '.', ''),
            'class' => 'text-muted fw-700',
        ];

        $records['total'] = [
            'title' => 'TOTAL Sum USD',
            'rows' => $rows
        ];

        return $records;
    }

    private function byManagerStatusGroupReports($records, $user_id)
    {
        $groups = StatusGroup::where('in_report', 1)->get();

        foreach ($groups as $group) {
            $statuses_in = Status::where('group_id', $group->id)->get('id')->pluck('id')->all();

            $rows = [];
            $total = 0;
            foreach ($this->rangeDates as $date) {
                $res = StatusChangeHistory::whereBetween('created_at', ["{$date} 00:00:00", "{$date} 23:59:59"])
                    ->whereUserId($user_id)->whereIn('new_status', $statuses_in)->count();
                $total += $res;
                $rows[$date] = [
                    'value' => $res,
                ];
            }
            $rows['act'] = [
                'value' => $total,
                'class' => 'fw-700',
            ];
            $rows['plan'] = [
                'value' => 55,
                'class' => 'text-muted fw-700',
            ];
            $rows['act_per'] = [
                'value' => number_format(($total / $rows['plan']['value'] * 100), 2, '.', ''),
                'class' => 'text-muted fw-700',
            ];

            $records[] = [
                'title' => $group->title,
                'rows' => $rows
            ];
        }

        return $records;
    }

    /**
     * Отчет по Каналам с разбивкой по подразделению.
     */
    private function reportByBranch(): array
    {
        $headerRecords = [
            [
                'value' => 'By Branch',
                'class' => 'fs-xl',
            ],
            [
                'value' => 'Total',
                'class' => 'fs-md',
            ],
            [
                'value' => 'DYNAMICs',
                'class' => 'fs-md',
            ],
        ];
        foreach ($this->rangeDates as $dt) {
            $headerRecords [] = [
                'value' => date('m/d', strtotime($dt)),
                'class' => 'fs-sm',
            ];
        }
        $records = $this->reportByBranchRecords();

        return [
            'header' => [
                'records' => $headerRecords
            ],
            'records' => $records,
        ];
    }

    private function reportByBranchRecords(): array
    {
        $branches = Division::get(['id', 'title']);
        $this->source2name = Order\Source::get(['id', 'title'])->pluck('title', 'id')->all();


        $preloadData = $this->byBranchPreloadData($this->rangeDates);


        $date_start = Carbon::parse(reset($this->rangeDates))->subDays(count($this->rangeDates));
        $date_end = Carbon::parse(reset($this->rangeDates));
        $range = new DatePeriod($date_start, new DateInterval('P1D'), $date_end);
        $rangePrevDates = [];
        foreach ($range as $dt) {
            $rangePrevDates[] = $dt->format('Y-m-d');
        }
        $preloadPrevData = $this->byBranchPreloadData($rangePrevDates);

        $headerRecords = [
            [
                'value' => 'Fake 11',
                'class' => 'fw-700',
            ],
            [
                'value' => 'Fake +2',
                'class' => 'text-muted fw-700',
            ]
        ];
        foreach ($this->rangeDates as $dt) {
            $weekDayName = date('D', strtotime($dt));

            $headerRecords [] = [
                'value' => $weekDayName,
                'class' => 'weekdays week-'.$weekDayName,
            ];
        }

        $report = [];
        foreach ($branches as $branch) {
            $records = [];
            $records = $this->reportByBranchReport($records, $branch->id, $preloadData, $preloadPrevData);

            $headerRecords[0]['value'] = $preloadData[$branch->id]['total'] ?? 0;

            $prev = $preloadPrevData[$branch->id]['total'] ?? 0;
            $headerRecords[1]['value'] = $prev;


            $v = [
                'title' => $branch->title,
                'expanded' => false,
                'header' => $headerRecords,
                'records' => $records,
                'hasRecords' => (bool) count($records),
            ];

            $report[] = $v;
        }

        return $report;
    }

    private function byBranchPreloadData(array $rangeDates): array
    {
        $preloadData = [];
        foreach ($rangeDates as $date) {
            $orders = Order::whereBetween('created_at', ["{$date} 00:00:00", "{$date} 23:59:59"])
                ->get(['id', 'division_id', 'user_id', 'source_id']);

            foreach ($orders as $v) {
                if (!$v->division_id || !$v->user_id || !$v->source_id) {
                    continue;
                }

                if (!isset($preloadData[$v->division_id]['total'])) {
                    $preloadData[$v->division_id]['total'] = 0;
                }
                if (!isset($preloadData[$v->division_id]['records'][$v->source_id])) {
                    $preloadData[$v->division_id]['records'][$v->source_id] = [
                        'total' => 0,
                        'records' => [],
                        'managerIds' => []
                    ];
                }
                if (!isset($preloadData[$v->division_id]['records'][$v->source_id]['records'][$date])) {
                    $preloadData[$v->division_id]['records'][$v->source_id]['records'][$date] = [
                        'total' => 0,
                        'byManager' => [],
                    ];
                }

                $preloadData[$v->division_id]['total']++;
                $preloadData[$v->division_id]['records'][$v->source_id]['total']++;
                $preloadData[$v->division_id]['records'][$v->source_id]['records'][$date]['total']++;
                if (!isset($preloadData[$v->division_id]['records'][$v->source_id]['records'][$date]['byManager'][$v->user_id])) {
                    $preloadData[$v->division_id]['records'][$v->source_id]['records'][$date]['byManager'][$v->user_id] = 1;
                } else {
                    $preloadData[$v->division_id]['records'][$v->source_id]['records'][$date]['byManager'][$v->user_id]++;
                }
                if (!in_array($v->user_id, $preloadData[$v->division_id]['records'][$v->source_id]['managerIds'],
                    true)) {
                    $preloadData[$v->division_id]['records'][$v->source_id]['managerIds'][] = $v->user_id;
                }
            }
        }

        return $preloadData;
    }

    private function reportByBranchReport(array $records, int $branch_id, array $preloadData, array $preloadPrevData)
    {
        if (!isset($preloadData[$branch_id]['records'])) {
            return $records;
        }

        foreach ($preloadData[$branch_id]['records'] as $source_id => $report) {
            $rows = [
                [
                    'value' => $report['total'],
                ],
                [
                    'value' => $preloadPrevData[$branch_id]['records'][$source_id]['total'] ?? '0',
                ]
            ];

            foreach ($this->rangeDates as $dt) {
                $rows[] = [
                    'value' => $report['records'][$dt]['total'] ?? 0,
                ];
            }

            // Детально по манагерам
            $sub_records = [];
            $sum = 0;
            foreach ($preloadData[$branch_id]['records'][$source_id]['managerIds'] as $managerId) {
                $sub_rows = [
                    [
                        'value' => $this->managers[$managerId]->name ?? 'n/a',
                        'class' => 'record-first-row-2',
                    ],
                    [
                        'value' => 'total'
                    ],
                    [
                        'value' => '-'
                    ],
                ];

                foreach ($this->rangeDates as $dt) {
                    $val = $report['records'][$dt]['byManager'][$managerId] ?? 0;
                    $sub_rows[] = [
                        'value' => $val,
                    ];
                    $sum += $val;
                }
                $sub_rows[1]['value'] = $sum;

                $sub_records[] = [
                    'rows' => $sub_rows,
                ];
            }

            $records[] = [
                'title' => $this->source2name[$source_id],
                'expanded' => false,
                'rows' => $rows,
                'records' => $sub_records,
            ];
        }

        return $records;
    }
}
