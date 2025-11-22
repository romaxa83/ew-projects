<?php

namespace App\Console\Commands\Info;

use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceHistory;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;

class DeviceHistoryInfo extends Command
{
    protected $signature = 'info:device_history {--last=}';

    public function handle()
    {
        $limit = $this->option('last') ?? 5;

        $id = $this->ask('Enter ID Device');

        $model = Device::query()
            ->withTrashed()
            ->with([
                'company',
                'company.subscription',
                'company.gpsDeviceSubscription',
                'histories' => function($q) use ($limit) {
                    $q->orderByDesc('created_at')->limit($limit);
                }
            ])
            ->where('id', $id)
            ->first();

        if(!$model){
            $this->error("Not found device by id [$id]");
            return 0;
        }

        $this->infoDevice($model);
        $this->history($model->histories);

        $this->historyCount(
            DeviceHistory::query()
            ->select('context', \DB::raw('COUNT (*) as count'))
            ->where('device_id', $id)
            ->groupBy('context')
            ->get()
        );
    }

    private function infoDevice(Device $model): void
    {
        $data = [
            'Name: ' => $model->name,
            'Imei: ' => $model->imei,
            'Status: ' => $model->status,
            'Status request: ' => $model->status_request,
            'Status activate request: ' => $model->status_activate_request,
            'Active Date: ' => $model->active_at,
            'Inactive Date: ' => $model->inactive_at,
            'Active till Date: ' => $model->active_till_at,
            'Truck: ' => $model->truck
                ? $model->truck->unit_number . ' ['. $model->truck->id .']'
                : null,
            'Trailer: ' => $model->trailer
                ? $model->trailer->unit_number . ' ['. $model->trailer->id .']'
                : null,
            '===============================================' => '',
            'Company: ' => $model->company
                ? $model->company->name . ' ['. $model->company->id .']'
                : null,
            'Company billing start: ' => $model->company
                ? $model->company->subscription
                    ? $model->company->subscription->billing_start
                    : null
                : null,
            'Company billing end: ' => $model->company
                ? $model->company->subscription
                    ? $model->company->subscription->billing_end
                    : null
                : null,
            'Company subscription cancel: ' => $model->company
                ? $model->company->subscription
                    ? $this->boolAsString($model->company->subscription->canceled)
                    : null
                : null,
            '================================================' => '',
            'GPS subscription id: ' => $model->company
                ? $model->company->gpsDeviceSubscription
                    ? $model->company->gpsDeviceSubscription->id
                    : null
                : null,
            'GPS subscription status: ' => $model->company
                ? $model->company->gpsDeviceSubscription
                    ? $model->company->gpsDeviceSubscription->status
                    : null
                : null,
            'GPS subscription rate: ' => $model->company
                ? $model->company->gpsDeviceSubscription
                    ? $model->company->gpsDeviceSubscription->current_rate
                    : null
                : null,
        ];

        foreach ($data as $key => $value){
            $this->line('<bg=green> '. $key .' </><bg=green;fg=black>'. $value .'</>');
        }
    }

    private function historyCount(\Illuminate\Database\Eloquent\Collection $collection): void
    {
        foreach ($collection as $item){
            $this->line($item->context .' : ['. $item->count .']');
        }
    }

    private function boolAsString(bool $value): string
    {
        if($value){
            return 'true';
        }

        return 'false';
    }

    private function history(Collection $collection)
    {
        $this->registerCustomTableStyle();
        $headers = [
            [new TableCell("История изменения девайса", ['colspan' => 2])],
            ['action', 'date', 'old_value', 'new_value']
        ];

        $data = $collection
            ->map(function (DeviceHistory $model) {
                return [
                    'action' => $model->context,
                    'date' => $model->created_at->toDateTimeString(),
                    'old_data' => $this->normalizeArrayData($model->changed_data['old']),
                    'new_data' => $this->normalizeArrayData($model->changed_data['new'])
                ];
            });


        $data->push(new TableSeparator());
        $data->push([new TableCell(
            sprintf('%f%% готовых к отправке по отношению ко всем отчетам', 100),
            ['colspan' => 4]
        )]);

        $this->table($headers, $data, 'secrets');
    }

    private function normalizeArrayData(array $data): string
    {
        $str = 'NONE';
        if(!empty($data)){
            $tmp = [];

            foreach ($data as $key => $value){
                $pattern = '/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d+Z/';
                preg_match($pattern, $value, $matches);
                if (isset($matches[0])) {
                    $value = Carbon::create($matches[0])->toDateTimeString();
                }
                $tmp[$key] = $value;
            }
            $tmp = array_to_json($tmp);
            $str = substr(substr(str_replace(',', PHP_EOL, $tmp), 1), 0, -1);
        }

        return $str;
    }


    private function registerCustomTableStyle()
    {
        $tableStyle = (new TableStyle())
            ->setCellHeaderFormat('<fg=black;bg=yellow>%s</>')
        ;
        Table::setStyleDefinition('secrets', $tableStyle);
    }
}

