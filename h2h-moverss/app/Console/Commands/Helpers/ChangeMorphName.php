<?php

namespace App\Console\Commands\Helpers;

use App\Models\Attachment;
use App\Models\Audit;
use App\Models\Communications\ConversationFavorites;
use App\Models\Communications\ConversationMark;
use App\Models\DispatchEmployer;
use App\Models\DispatchTruck;
use App\Models\Mailbox\Gmail\Message;
use App\Models\Material;
use App\Models\Order;
use App\Models\Ringostat\EventAfterCall;
use App\Models\Tasks\Task;
use App\Models\Truck\Truck;
use App\Models\Twilio\TwilioSms;
use App\Models\Zadarma\CallsEvents;
use App\Models\Zadarma\SmsEvents;
use Illuminate\Console\Command;
use App\Models\Client;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\Console\Helper\ProgressBar;

class ChangeMorphName extends Command
{
    private $data = [
        'App\Models\Communications\ConversationFavorites' => ConversationFavorites::MORPH_NAME,
        'App\Models\Communications\ConversationMark' => ConversationMark::MORPH_NAME,
        'App\Models\Mailbox\Gmail\Message' => Message::MORPH_NAME,
        'App\Models\Order' => Order::MORPH_NAME,
        'App\Models\Order\Notes' => Order\Notes::MORPH_NAME,
        'App\Models\Order\Activity' => Order\Activity::MORPH_NAME,
        'App\Models\Order\Work' => Order\Work::MORPH_NAME,
        'App\Models\Order\Waypoint' => Order\Waypoint::MORPH_NAME,
        'App\Models\Order\WaypointNotes' => Order\WaypointNotes::MORPH_NAME,
        'App\Models\Order\Estimate' => Order\Estimate::MORPH_NAME,
        'App\Models\Order\Estimate\Interstate' => Order\Estimate\Interstate::MORPH_NAME,
        'App\Models\Order\Estimate\Intrastate' => Order\Estimate\Intrastate::MORPH_NAME,
        'App\Models\Order\Estimate\Local' => Order\Estimate\Local::MORPH_NAME,
        'App\Models\Order\Estimate\Calculated' => Order\Estimate\Calculated::MORPH_NAME,
        'App\Models\Order\Payment' => Order\Payment::MORPH_NAME,
        'App\Models\Order\Inventory' => Order\Inventory::MORPH_NAME,
        'App\Models\Order\Material' => Order\Material::MORPH_NAME,
        'App\Models\Order\Extended' => Order\Extended::MORPH_NAME,
        'App\Models\Material' => Material::MORPH_NAME,
        'App\Models\Order\CustomExtra' => Order\CustomExtra::MORPH_NAME,
        'App\Models\Task' => Task::MORPH_NAME,
        'App\Models\Truck' => Truck::MORPH_NAME,
        'App\Models\Ringostat\EventAfterCall' => EventAfterCall::MORPH_NAME,
        'App\Models\Client\Activity' => Client\Activity::MORPH_NAME,
        'App\Models\Twilio\TwilioSms' => TwilioSms::MORPH_NAME,
        'App\Models\Zadarma\CallsEvents' => CallsEvents::MORPH_NAME,
        'App\Models\Zadarma\SmsEvents' => SmsEvents::MORPH_NAME,
        'App\Models\Client' => Client::MORPH_NAME,
        'App\Models\Client\Phone' => Client\Phone::MORPH_NAME,
        'App\Models\Client\Email' => Client\Email::MORPH_NAME,
        'App\Models\Client\Messenger' => Client\Messenger::MORPH_NAME,
        'App\Models\Client\Notes' => Client\Notes::MORPH_NAME,
        'App\Models\Attachment' => Attachment::MORPH_NAME,
        'App\Models\DispatchEmployer' => DispatchEmployer::MORPH_NAME,
        'App\Models\DispatchTruck' => DispatchTruck::MORPH_NAME,
    ];

    protected $signature = 'helpers:change_morph_name';

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
        foreach ($this->data as $old => $new) {
            $this->audit($old, $new);
        }
    }

    public function audit($old, $new)
    {
        $chunk = 500;
        $query = Audit::query()
            ->where('auditable_type', $old)
        ;
        $queryCount = $query->count();

        $this->info('AUDIT - ' .$old . " [$queryCount]");

        $progressBar = new ProgressBar($this->output, $queryCount);
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, $new) {
                $progressBar->advance($chunk);

                $ids = $items->pluck('id')->toArray();
                Audit::whereIn('id', $ids)
                    ->update(['auditable_type' => $new]);
            });

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
    }
}


