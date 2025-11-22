<?php

namespace App\Console\Commands\Info;

use App\Models\GPS\History;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;

class GpsHistoryInfo extends Command
{
    protected $signature = 'info:gps_history {--last=}';

    public function handle()
    {
        $limit = $this->option('last') ?? 10;

        $id = $this->ask('Enter ID Truck');

        $histories = History::query()
            ->with(['alerts'])
            ->where('truck_id', $id)
            ->orderBy('received_at', 'desc')
            ->limit($limit)
            ->get();

        $this->history($histories);

    }

    private function history(Collection $collection)
    {
        $this->registerCustomTableStyle();
        $headers = [
            [new TableCell("История изменения девайса", ['colspan' => 2])],
            ['id', 'type', 'received_at', 'speed', 'alerts']
        ];

        $data = $collection
            ->map(function (History $model) {
                return [
                    'id' => $model->id,
                    'received_at' => $model->received_at->timezone("EET"),
                    'type' => $model->event_type,
                    'speed' => $model->speed,
                    'alerts' => $this->alertAsStr($model),
                ];
            });


        $data->push(new TableSeparator());

        $this->table($headers, $data, 'secrets');
    }

    public function alertAsStr(History $model)
    {
        return implode(' ,', $model->alerts->pluck('alert_type')->toArray());
    }


    private function registerCustomTableStyle()
    {
        $tableStyle = (new TableStyle())
            ->setCellHeaderFormat('<fg=black;bg=yellow>%s</>')
        ;
        Table::setStyleDefinition('secrets', $tableStyle);
    }
}


