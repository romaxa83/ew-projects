<?php

namespace App\Console\Commands\Helpers\Communications;

use App\Models\Communications\CommunicationRecord;
use Illuminate\Console\Command;

class ShowCommunicationRecs extends Command
{
    protected $signature = 'helpers:communication_recs_show';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $id = $this->ask('ID');

        $model = CommunicationRecord::query()
            ->where('id', $id)
            ->first();

        if(!$model){
            $this->error("Not found by id [{$id}]");
            return self::FAILURE;
        }

        dd($model->entity);
    }
}

