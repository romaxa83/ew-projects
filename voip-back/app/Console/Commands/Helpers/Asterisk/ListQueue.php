<?php

namespace App\Console\Commands\Helpers\Asterisk;

use App\IPTelephony\Services\Storage\Asterisk\QueueService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;

class ListQueue extends Command
{
    protected $signature = 'asterisk:queue_list';

    public function handle(): int
    {
        $headers = [
            [new TableCell('A list of queue [asterisk]', ['colspan' => 2])],
            ['uuid', 'name']
        ];

        $tableStyle = (new TableStyle())
            ->setCellHeaderFormat('<fg=black;bg=yellow>%s</>');
        Table::setStyleDefinition('secrets', $tableStyle);

        $models = $this->getModels();
        $data = $models->map(function ($item){
            return [
                'uuid' => $item->uuid,
                'name' => $item->name,
            ];
        });

        $data->push(new TableSeparator());

        $this->table($headers, $data, 'secrets');

        return self::SUCCESS;
    }

    public function getModels(): Collection
    {
        /** @var $service QueueService */
        $service = resolve(QueueService::class);

        return $service->getAll();
    }
}
