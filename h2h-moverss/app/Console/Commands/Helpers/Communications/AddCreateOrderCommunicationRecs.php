<?php

namespace App\Console\Commands\Helpers\Communications;

use App\Enums\Orders\ActivityType;
use App\Models\Client\Activity;
use App\Models\Communications\CommunicationRecord;
use App\Models\Communications\ConversationMark;
use App\Models\Division;
use App\Models\Mailbox\Gmail\Account;
use App\Models\Mailbox\Gmail\Message;
use App\Models\Order;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Tasks\Task;
use App\Models\Twilio\TwilioSms;
use App\Models\Zadarma\CallsEvents;
use App\Models\Zadarma\SmsEvents;
use App\Services\Communications\RecordCreateService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Helper\ProgressBar;

class AddCreateOrderCommunicationRecs extends Command
{
    protected $signature = 'helpers:communication_recs_add_create_order';

    protected array $divisionMisc = [];

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
        $this->divisionMisc = Division::query()
            ->select(['miscs', 'id'])
            ->get()
            ->pluck('miscs', 'id')
            ->toArray()
        ;

        $this->order();
    }


    public function order()
    {
        $count = 0;
        $chunk = 200;

        $query = Order::query();

        $this->info('ORDER RECS');
        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, &$count) {
                $progressBar->advance($chunk);
                $items->each(function (Order $item) use ($progressBar, $chunk, &$count) {

                    if(
                        !\DB::table(CommunicationRecord::TABLE)
                            ->where('entity_type', Order::MORPH_NAME)
                            ->where('entity_id', $item->id)
                            ->exists()
                    ){

                        RecordCreateService::handler($item, [
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                        ]);

                        $count++;
                    }
                });
            });

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
        $this->info("ORDER RECS [{$count}]");
        echo PHP_EOL;
    }


}

