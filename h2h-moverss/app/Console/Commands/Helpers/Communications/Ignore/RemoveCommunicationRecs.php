<?php

namespace App\Console\Commands\Helpers\Communications\Ignore;

use App\Models\Communications\CommunicationRecord;
use App\Models\CommunicationsIgnoreList;
use App\Models\Mailbox\Gmail\Message;
use Illuminate\Console\Command;

// удаление по blaclist
class RemoveCommunicationRecs extends Command
{
    protected $signature = 'helpers:remove_communication_recs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $start = microtime(true);

            $this->exec();

            $time = microtime(true) - $start;

            echo PHP_EOL;
            $this->info("Done [time = {$time}]");
            echo PHP_EOL;

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }
    }

    public function exec()
    {
        $emails = CommunicationsIgnoreList::email()
            ->select('value')
            ->get()
            ->pluck('value')
            ->toArray()
        ;

        foreach ($emails as $email) {
            $count = CommunicationRecord::query()
                ->whereIn('entity_type', [
                    Message::MORPH_NAME
                ])
                ->where('channel_contact', $email)
                ->delete();

            $this->warn("[х] Remove com-records by channel_contact [{$email} - {$count}]");
        }

        return self::SUCCESS;
    }
}


