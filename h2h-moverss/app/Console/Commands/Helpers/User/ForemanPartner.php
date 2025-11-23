<?php

namespace App\Console\Commands\Helpers\User;

use App\Models\Employee;
use App\Models\Partners\Partner;
use App\User;
use Illuminate\Console\Command;

class ForemanPartner extends Command
{
    protected $signature = 'helpers:foreman_partner';

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
//        $model = Employee::query()
//            ->where('id', 176)
//            ->first();
//
//        dd($model->getPartnerHead());

        $partnerName = 'VHV';

        $partner = $this->getPartner($partnerName);
        $partnersData = $this->partnersData();

        foreach($partnersData as $data){

            $user = User::query()
                ->where('email', $data['email'])
                ->first();

            if($user->employee){
                $user->employee->partner_id = $partner->id;
                $user->employee->is_partner_head = $data['main'];
                $user->employee->save();

                $this->info('User ['.$user->email.'] updated');
            }
        }

    }

    private function partnersData(): array
    {
        return [
            [
                'main' => true,
                'email' => 'vhvolha@gmail.com',
            ],
            [
                'main' => false,
                'email' => 'alexey.kuryliuk@gmail.com',
            ],
            [
                'main' => false,
                'email' => 'sawaed7777777@gmail.com',
            ],
            [
                'main' => false,
                'email' => 'sasha92topol@gmail.com',
            ],
            [
                'main' => false,
                'email' => 'gazell347@gmail.com',
            ],
        ];
    }

    private function getPartner($name): Partner
    {
        $model = Partner::query()
            ->where('name', $name)
            ->first();


        if(!$model){
            $model = new Partner();
            $model->name = $name;
            $model->save();
        }

        return $model;
    }
}
