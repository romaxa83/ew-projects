<?php


namespace App\Console\Commands\Helpers\Kamailio;

use App\Helpers\DbConnections;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;

class ListLocation extends Command
{
    protected $signature = 'kamailio:location_list';
    public function handle(): int
    {
        $headers = [
            [new TableCell('A list of location', ['colspan' => 2])],
            ['username', 'contact']
        ];

        $tableStyle = (new TableStyle())
            ->setCellHeaderFormat('<fg=black;bg=yellow>%s</>');
        Table::setStyleDefinition('secrets', $tableStyle);

        $models = $this->getModels();
        $data = $models->map(function ($item){
            return [
                'username' => $item->username,
                'contact' => $item->contact,
            ];
        });

        $data->push(new TableSeparator());

        $this->table($headers, $data, 'secrets');

        return self::SUCCESS;
    }

    public function getModels(): Collection
    {
        return DbConnections::kamailio()
            ->table('location')
            ->get();
    }
}



