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

class CreateCommunicationRecs extends Command
{
    protected $signature = 'helpers:communication_recs';

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

//        $this->task();
        $this->order();
        $this->orderNote();
        $this->orderActivity();

//        $this->conversationMark();
        $this->email();

//        $this->ringostat();
//        $this->twilio();
//        $this->activity();
//        $this->zadarmCall();
//        $this->zadarmSms();
    }

    public function task()
    {
        $count = 0;
        $chunk = 300;

        $query = Task::query()
        ;

        $this->info('TASK RECS');
        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, &$count) {
                $progressBar->advance($chunk);
                $items->each(function (Task $item) use ($progressBar, $chunk, &$count) {

                    if(
                        !\DB::table(CommunicationRecord::TABLE)
                            ->where('entity_type', Task::MORPH_NAME)
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
        $this->info("TASK NOTE RECS [{$count}]");
        echo PHP_EOL;
    }

    public function orderNote()
    {
        $count = 0;
        $chunk = 300;

        $query = Order\Notes::query()->with(['order'])
        ;

        $this->info('ORDER NOTE RECS');
        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, &$count) {
                $progressBar->advance($chunk);
                $items->each(function (Order\Notes $item) use ($progressBar, $chunk, &$count) {

                    if(
                        !\DB::table(CommunicationRecord::TABLE)
                            ->where('entity_type', Order\Notes::MORPH_NAME)
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
        $this->info("ORDER NOTE RECS [{$count}]");
        echo PHP_EOL;
    }

    public function orderActivity()
    {
        $count = 0;
        $chunk = 300;
        $query = Order\Activity::query()
            ->with(['order'])
            ->whereIn('type', ActivityType::supportCommunicationPanel())
        ;

        $this->info('ORDER ACTIVITY RECS');
        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, &$count) {
                $progressBar->advance($chunk);
                $items->each(function (Order\Activity $item) use ($progressBar, $chunk, &$count) {

                    if(
                        !\DB::table(CommunicationRecord::TABLE)
                            ->where('entity_type', Order\Activity::MORPH_NAME)
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
        $this->info("ORDER ACTIVITY RECS [{$count}]");
        echo PHP_EOL;
    }

    public function order()
    {
        $count = 0;
        $chunk = 300;

        $query = Order::query()
            ->where('client_id', '!=', 0)
        ;

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

    public function conversationMark()
    {
        $count = 0;
        $chunk = 300;

        $query = ConversationMark::query()
        ;

        $this->info('Conversation Mark RECS');
        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, &$count) {
                $progressBar->advance($chunk);
                $items->each(function (ConversationMark $item) use ($progressBar, $chunk, &$count) {

                    if(
                        !\DB::table(CommunicationRecord::TABLE)
                            ->where('entity_type', ConversationMark::MORPH_NAME)
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
        $this->info("EMAIL RECS [{$count}]");
        echo PHP_EOL;
    }

    public function email()
    {
        $count = 0;
        $chunk = 300;

        $query = Message::query()
            ->whereIn('tag', [Message::TAG_SENT, Message::TAG_INBOX])
            ->where(function (Builder $q) {
                return $q->whereNotNull('miscs->to')
                    ->orWhereNotNull('miscs->from');
            })
        ;

        $accountIds = Account::query()
            ->select(['id', 'division_id'])
            ->get()
            ->pluck('division_id', 'id')
            ->toArray()
        ;

        $this->info('EMAIL RECS');
        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, &$count, $accountIds) {
                $progressBar->advance($chunk);
                $items->each(function (Message $item) use ($progressBar, $chunk, &$count, $accountIds) {

                    if(
                        !\DB::table(CommunicationRecord::TABLE)
                            ->where('entity_type', Message::MORPH_NAME)
                            ->where('entity_id', $item->id)
                            ->exists()
                    ){
                        $model = RecordCreateService::handler($item, [
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                            'division_id' => $accountIds[$item->account_id] ?? null,
                        ]);

                        if($model){
                            $model->refresh();
                            if(!$model->is_answered){
                                $time = new Carbon($model->entity->created_at, 'UTC');

                                if($this->isConversationRecordAnswered($model, $time)){
                                    $model->update(['is_answered' => true]);
                                }
                            }

                            $count++;
                        }
                    }
                });
            });

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
        $this->info("EMAIL RECS [{$count}]");
        echo PHP_EOL;
    }

    public function ringostat()
    {
        $divisions = [];

        foreach ($this->divisionMisc as $miscId => $misc) {
            if(!isset($misc['ringostat_project_id'])){
                throw new \Exception('Not ringostat_project_id');
            }
            $divisions[$misc['ringostat_project_id']] = $miscId;
        }

        $count = 0;
        $chunk = 300;

        $query = EventAfterCall::query()
        ;

        $this->info('RINGOSTAT RECS');
        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, &$count, $divisions) {
                $progressBar->advance($chunk);
                $items->each(function (EventAfterCall $item) use ($progressBar, $chunk, &$count, $divisions) {

                    if(
                        !\DB::table(CommunicationRecord::TABLE)
                        ->where('entity_type', EventAfterCall::MORPH_NAME)
                        ->where('entity_id', $item->id)
                        ->exists()
                    ){
                        $model = RecordCreateService::handler($item, [
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                            'division_id' => $divisions[$item->project_id] ?? null,
                        ]);

                        if($model){
                            $model->refresh();
                            if(!$model->is_answered){
                                $timestampSeconds = (int)($model->entity->call_timestamp / 1000000);
                                $microseconds = $model->entity->call_timestamp % 1000000;
                                $time = Carbon::createFromTimestamp($timestampSeconds)->addMicroseconds($microseconds);

                                if($this->isConversationRecordAnswered($model, $time)){
                                    $model->update(['is_answered' => true]);
                                }
                            }
                        }

                        $count++;
                    }
                });
            });

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
        $this->info("RINGOSTAT RECS [{$count}]");
        echo PHP_EOL;
    }

    public function twilio()
    {
        $count = 0;
        $chunk = 500;

        $query = TwilioSms::query()
        ;

        $this->info('TWILIO RECS');
        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, &$count) {
                $progressBar->advance($chunk);
                $items->each(function (TwilioSms $item) use ($progressBar, $chunk, &$count) {

                    if(
                        !\DB::table(CommunicationRecord::TABLE)
                            ->where('entity_type', TwilioSms::MORPH_NAME)
                            ->where('entity_id', $item->id)
                            ->exists()
                    ){
                        $model = RecordCreateService::handler($item, [
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                        ]);

                        if($model){
                            $model->refresh();
                            if(!$model->is_answered){
                                $time = new Carbon($model->entity->created_at, 'UTC');

                                if($this->isConversationRecordAnswered($model, $time)){
                                    $model->update(['is_answered' => true]);
                                }
                            }

                            $count++;
                        }
                    }
                });
            });

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
        $this->info("TWILIO RECS [{$count}]");
        echo PHP_EOL;
    }

    public function activity()
    {
        $count = 0;
        $chunk = 300;

        $query = Activity::query()
            ->where('type', 'customer.inventory.save')
        ;

        $this->info('CLIENT ACTIVITY RECS');
        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, &$count) {
                $progressBar->advance($chunk);
                $items->each(function (Activity $item) use ($progressBar, $chunk, &$count) {
                    if(
                        !\DB::table(CommunicationRecord::TABLE)
                            ->where('entity_type', Activity::MORPH_NAME)
                            ->where('entity_id', $item->id)
                            ->exists()
                    ){
                        $model = RecordCreateService::handler($item, [
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                        ]);

                        if($model){
                            $model->refresh();
                            if(!$model->is_answered){
                                if($this->isConversationRecordAnswered($model, $model->created_at)){
                                    $model->update(['is_answered' => true]);
                                }
                            }
                        }

                        $count++;
                    }
                });
            });

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
        $this->info("CLIENT ACTIVITY [{$count}]");
        echo PHP_EOL;
    }

    public function zadarmCall()
    {
        $divisions = [];
        foreach ($this->divisionMisc as $miscId => $misc) {
            if(!isset($misc['zadarma_pbx_id'])){
                throw new \Exception('Not zadarma_pbx_id');
            }
            $divisions[$misc['zadarma_pbx_id']] = $miscId;
        }

        $count = 0;
        $chunk = 400;

        $query = CallsEvents::query()
            ->whereIn('event', ['NOTIFY_OUT_END', 'NOTIFY_END'])
        ;

        $this->info('ZADARMA CALL RECS');
        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, &$count, $divisions) {
                $progressBar->advance($chunk);
                $items->each(function (CallsEvents $item) use ($progressBar, $chunk, &$count, $divisions) {
                    if(
                        !\DB::table(CommunicationRecord::TABLE)
                            ->where('entity_type', CallsEvents::MORPH_NAME)
                            ->where('entity_id', $item->id)
                            ->exists()
                    ){
                        $model = RecordCreateService::handler($item, [
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                            'division_id' => $divisions[$item->pbx_id] ?? null,
                        ]);

                        if($model){
                            $model->refresh();
                            if(!$model->is_answered){
                                $time = (new Carbon($model->entity->call_start,config('app.timezone')))->setTimezone('UTC');

                                if($this->isConversationRecordAnswered($model, $time)){
                                    $model->update(['is_answered' => true]);
                                }
                            }
                        }

                        $count++;
                    }
                });
            });

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
        $this->info("ZADARMA CALL [{$count}]");
        echo PHP_EOL;
    }

    public function zadarmSms()
    {
        $divisions = [];
        foreach ($this->divisionMisc as $miscId => $misc) {
            if(!isset($misc['zadarma_pbx_id'])){
                throw new \Exception('Not zadarma_pbx_id');
            }
            $divisions[$misc['zadarma_pbx_id']] = $miscId;
        }

        $count = 0;
        $chunk = 300;

        $query = SmsEvents::query();

        $this->info('ZADARMA SMS RECS');
        $progressBar = new ProgressBar($this->output, $query->count());
        $progressBar->setFormat('verbose');
        $progressBar->start();
        try {
            $query->chunk($chunk, function (Collection $items) use ($progressBar, $chunk, &$count, $divisions) {
                $progressBar->advance($chunk);
                $items->each(function (SmsEvents $item) use ($progressBar, $chunk, &$count, $divisions) {
                    if(
                        !\DB::table(CommunicationRecord::TABLE)
                            ->where('entity_type', SmsEvents::MORPH_NAME)
                            ->where('entity_id', $item->id)
                            ->exists()
                    ){
                        $model = RecordCreateService::handler($item, [
                            'created_at' => $item->created_at,
                            'updated_at' => $item->updated_at,
                            'division_id' => $divisions[$item->pbx_id] ?? null,
                        ]);

                        if($model){
                            $model->refresh();
                            if(!$model->is_answered){
                                $time = new Carbon($model->entity->created_at, 'UTC');

                                if($this->isConversationRecordAnswered($model, $time)){
                                    $model->update(['is_answered' => true]);
                                }
                            }
                        }

                        $count++;
                    }
                });
            });

        } catch (\Throwable $e) {
            dd($e->getMessage());
        }

        $progressBar->finish();
        echo PHP_EOL;
        $this->info("ZADARMA SMS [{$count}]");
        echo PHP_EOL;
    }

    private function isConversationRecordAnswered(CommunicationRecord $model, $time): bool
    {
        $isAnswered = $model->is_answered;

        if (!$model->is_answered) {
            // check for marks
            if ($model->client) {
                if (ConversationMark::where('client_id', $model->client_id)
                    ->where('type', 'read')
                    ->where('created_at', '>', $time)->count())
                    $isAnswered = true;
            } else {
                if (
                    $model->isRingostatCall()
                    || $model->isTwilioSms()
                    || $model->isZadarmaCall()
                ) {
                    if (
                        ConversationMark::where('contact_type', 'phone')
                            ->where('type', 'read')
                            ->where('contact_value', 'like', '%' . $model->channel_contact)
                            ->where('created_at', '>', $time)
                            ->count()
                    )
                        $isAnswered = true;
                }
                if(
                    $model->isGmailMsg()
                ){
                    if (
                        ConversationMark::where('contact_type', 'email')
                            ->where('type', 'read')
                            ->where('contact_value', 'like', '%' . $model->channel_contact)
                            ->where('created_at', '>', $time)
                            ->count()
                    )
                        $isAnswered = true;
                }
            }
        }

        return $isAnswered;
    }
}

