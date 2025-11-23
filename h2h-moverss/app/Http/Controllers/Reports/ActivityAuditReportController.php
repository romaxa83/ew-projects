<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Providers\AppServiceProvider;
use App\User;
use Illuminate\Database\Eloquent\{Builder, Model};
use Illuminate\Http\Request;
use App\Models\{Audit};
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\{Arr, Str, Collection, Facades\File, Facades\Validator};
use Illuminate\Container\Container;
use OwenIt\Auditing\Contracts\Auditable;
use Yajra\DataTables\{EloquentDataTable, Facades\DataTables};

class ActivityAuditReportController extends Controller
{
    public function view(Request $request)
    {
        $filter = $request->get('filter');
        if (!empty($filter['from'])) {
            $validator = Validator::make($filter, [
                'name' => 'from|date_format:Y-m-d:',
            ]);
            if ($validator->fails()) {
                unset($filter['from']);
            }
        }

        $divisionMiscs = session()->get('division.miscs');


        $Users = User::whereJsonContains('division_ids', $request->session()->get('division.id'))
            ->with('employee')
            ->get(['id', 'name'])
            ->map(function ($u) {
                $name = $u->name;
                if ($u->employee) {
                    $name = $u->employee->name . ' ' . $u->employee->l_name;
                }
                return collect(['id' => $u->id, 'name' => $name]);
            });

        return view('layouts.reports.activity-audit', [
            'users' => $Users->toArray(),
            'objects' => $this->getAuditableModels(),
            'events' => ['created', 'updated', 'deleted', 'sync'],
            'filter' => !empty($filter) ? $filter : []
        ]);
    }


    private function getAuditableModels()
    {
        return collect(File::allFiles(app_path()))
            ->map(function ($item) {
                $path = $item->getRelativePathName();
                $class = sprintf('\%s%s',
                    Container::getInstance()->getNamespace(),
                    strtr(substr($path, 0, strrpos($path, '.')), '/', '\\'));

                return $class;
            })
            ->filter(function ($class) {
                $valid = false;
                if (class_exists($class)) {
                    $reflection = new \ReflectionClass($class);
                    $valid = $reflection->isSubclassOf(Model::class) && $reflection->implementsInterface(Auditable::class) &&
                        !$reflection->isAbstract();
                }
                return $valid;
            })->map(function ($v) {
                $text = Str::replace('\\App\\Models\\', '', $v);
                return ['id' => $text, 'text' => $text];
            });
    }


    private function extendUserTimeData($userData, $roundTo)
    {
        ksort($userData);
        $keys = array_keys($userData);
        foreach (new \DatePeriod(
                     Carbon::createFromTimestampMs($keys[0]),
                     \DateInterval::createFromDateString($roundTo . ' minutes'),
                     Carbon::createFromTimestampMs(end($keys))) as $DT) {
            $Carbon = new Carbon($DT);
            if (!array_key_exists($Carbon->getTimestampMs(), $userData)) {
                $userData[$Carbon->getTimestampMs()] = 0;
            }
        }
        ksort($userData);
        return $userData;
    }

    /**
     * @return void
     */
    private function plotData(Builder $Audit)
    {
        // приводим данные к таймзоне проекта
        $divisionMiscs = session()->get('division.miscs');
        $byUsersRounded = [];
        $byUsersSeries = [];

//        $fromDT = Carbon::createFromFormat('Y-m-d H:i:s', '2022-11-14 00:00:00', $divisionMiscs['tz'])
//            ->setTimezone('UTC');
//        $toDT = Carbon::createFromFormat('Y-m-d H:i:s', '2022-11-14 23:59:59', $divisionMiscs['tz'])
//            ->setTimezone('UTC');
//        dd(Str::replaceArray('?', $Audit->getBindings(), $Audit->toSql()));
        $AuditData = $Audit->where('user_id', '>', 0)
            ->get(['user_id', 'created_at']);
//        dd($AuditData->count());
        if ($AuditData->isNotEmpty()) {
            $byUser = $AuditData->reduce(function ($carry, $record) use ($divisionMiscs) {
                $dt = (new Carbon($record->created_at, new DateTimeZone('UTC')))->setTimezone(new DateTimeZone($divisionMiscs['tz']));
                $carry[$record->user_id][$dt->format('Y-m-d H:i:s')] = 1;
                return $carry;
            }, []);

            if (!empty($byUser))
                foreach ($byUser as $userID => $userData) {
                    foreach ($userData as $dt => $dtV) {
                        $DT = Carbon::createFromFormat('Y-m-d H:i:s', $dt, new DateTimeZone('UTC'));
                        $rounded = $this->roundToMinutes($DT->getTimestamp(), 5) * 1000;
                        if (!Arr::has($byUsersRounded, $userID . '.' . $rounded))
                            $byUsersRounded = Arr::add($byUsersRounded, $userID . '.' . $rounded, 1);
                        $byUsersRounded[$userID][$rounded] += 1;
                    }
                }
            //
            foreach ($byUsersRounded as $userID => $userData) {
                $User = User::find($userID, ['id', 'name']);
                $series = ['user' => $User, 'label' => $User->name, 'name' => $User->name];

                foreach ($this->extendUserTimeData($userData, 5) as $k => $v) {
                    $series['data'][] = [$k, $v];
                }
                $byUsersSeries[] = $series;
            }
        }
//
        return $byUsersSeries;
    }

    /**
     * @param $seconds
     * @param $roundToMinutes
     * @return float|int
     */
    private function roundToMinutes($seconds, $roundToMinutes)
    {
        return round($seconds / ($roundToMinutes * 60)) * ($roundToMinutes * 60);
    }


    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function datatable(Request $request)
    {
        $P = $request->validate([
            'filter.start-range' => 'required|date_format:Y-m-d',
            'filter.end-range' => 'required|date_format:Y-m-d',
            'filter.author' => "nullable|array",
            'filter.author.*' => "nullable|int",
            'filter.order' => "nullable|array",
            'filter.order.*' => "nullable|int",
            'filter.client' => "nullable|array",
            'filter.client.*' => "nullable|int",
            'filter.object' => "nullable|array",
            'filter.object.*' => "nullable|string",
            'filter.event' => "nullable|array",
            'filter.event.*' => "nullable|string",
        ]);

        $divisionMiscs = session()->get('division.miscs');

        $Audit = Audit::with(['user' => function ($q) {
            $q->whereJsonContains('division_ids', session()->get('division.id'))->select(['id', 'name']);
        }])
            ->whereIn('user_id', User::whereJsonContains('division_ids', session()->get('division.id'))->get(['id'])->pluck('id')->toArray())
            ->where(function (Builder $q) use ($P) {
//            $q->whereBetween('created_at', [Carbon::parse($P['filter']['start-range'] . ' 00:00:00'),
//                Carbon::parse($P['filter']['end-range'] . ' 23:59:59')]);
                if (!empty($P['filter']['order']))
                    $q->whereIn('order_id', $P['filter']['order']);
                if (!empty($P['filter']['client']))
                    $q->whereIn('client_id', $P['filter']['client']);
                if (!empty($P['filter']['author']))
                    $q->whereIn('user_id', $P['filter']['author']);
                if (!empty($P['filter']['event']))
                    $q->whereIn('event', $P['filter']['event']);
                if (!empty($P['filter']['object'])) {
                    foreach ($P['filter']['object'] as &$model) {
                        $model = 'App\\Models\\' . $model;
                        $q->whereIn('auditable_type', $P['filter']['object']);
                    }
                }
            });

        $fromDT = Carbon::parse($P['filter']['start-range'] . ' 00:00:00', new DateTimeZone($divisionMiscs['tz']))
            ->setTimezone(new DateTimeZone('UTC'));
        $toDT = Carbon::parse($P['filter']['end-range'] . ' 23:59:59', new DateTimeZone($divisionMiscs['tz']))
            ->setTimezone(new DateTimeZone('UTC'));

        $plotFromDT = (Carbon::parse($P['filter']['end-range'] . ' 23:59:59', new DateTimeZone($divisionMiscs['tz'])))
            ->modify('yesterday 00:00')->setTimezone(new DateTimeZone('UTC'));;
//        dump($plotFromDT)->setTimezone(new DateTimeZone($divisionMiscs['tz']));
//        dd($toDT->setTimezone(new DateTimeZone($divisionMiscs['tz'])));
        /**
         * @var $EloquentDataTable EloquentDataTable
         */
        $EloquentDataTable = Datatables::of($Audit->whereBetween('created_at',
            [$fromDT, $toDT]));

        return $EloquentDataTable
            ->addColumn('old_values_cutted', function ($record) {
                return Str::limit(array_to_json($record->old_values), 40);
            })
            ->addColumn('new_values_cutted', function ($record) {
                return Str::limit(array_to_json($record->new_values), 40);
            })
//            ->editColumn('updated_at', function ($record) {
//                return $record->updated_at ? with(new Carbon($record->updated_at))->format('M d, Y g:i A') : '';
//            })
            ->addColumn('updated_at_division_tz', function ($record) use ($divisionMiscs) {
                return (new Carbon($record->updated_at, new DateTimeZone('UTC')))
                    ->setTimezone($divisionMiscs['tz'])->format('M d, Y g:i A');
            })
            ->editColumn('user_id', function ($record) {
                if ($record->user)
                    return $record->user->name;
                return $record->user_id;
            })
            ->editColumn('auditable_type', function ($record) {

                return isset(AppServiceProvider::morphs()[$record->auditable_type])
                    ? AppServiceProvider::morphs()[$record->auditable_type]. ' [' . $record->auditable_id . ']'
                    : $record->auditable_type . ' [' . $record->auditable_id . ']';

//                return Str::replace('App\\Models\\', '', $record->auditable_type) . ' [' . $record->auditable_id . ']';
            })
            ->with('plot', $this->plotData(
                Audit::whereIn('user_id', User::whereJsonContains('division_ids', session()->get('division.id'))->get(['id'])->pluck('id')->toArray())
                    ->whereBetween('created_at', [$plotFromDT, $toDT])))
            ->make(true);
    }
}
