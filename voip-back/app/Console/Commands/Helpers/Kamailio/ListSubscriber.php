<?php


namespace App\Console\Commands\Helpers\Kamailio;

use App\Helpers\DbConnections;
use App\IPTelephony\Entities\Kamailio\SubscriberEntity;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Helper\TableStyle;

class ListSubscriber extends Command
{
    protected $signature = 'kamailio:subscriber_list';
    public function handle(): int
    {
        $headers = [
            [new TableCell('A list of all subscriber', ['colspan' => 5])],
            ['id', 'guid', 'username', 'domain', 'password']
        ];

        $tableStyle = (new TableStyle())
            ->setCellHeaderFormat('<fg=black;bg=yellow>%s</>');
        Table::setStyleDefinition('secrets', $tableStyle);

        $hasUuid = 0;
        $users = $this->getSubscriber();
        $data = $users->map(function ($item) use (&$hasUuid) {
            if($item->uuid){
                $hasUuid++;
            }

            return [
                'id' => $item->id,
                'guid' => $item->uuid,
                'username' => $item->username,
                'domain' => $item->domain,
                'password' => $item->password,
            ];
        });

        $data->push(new TableSeparator());
        $data->push([new TableCell(
            sprintf('%d has uuid', $hasUuid),
            ['colspan' => 5]
        )]);

        $this->table($headers, $data, 'secrets');

        return self::SUCCESS;
    }

    public function getSubscriber(): Collection
    {
        return DbConnections::kamailio()
            ->table(SubscriberEntity::TABLE)
            ->get();
    }
}


