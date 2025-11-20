<?php

namespace WezomCms\Requests\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use WezomCms\Requests\Services\Request1CService;
use WezomCms\Users\Models\Car;
use WezomCms\Users\Services\UserCarService;
use WezomCms\Users\Services\UserService;

class VerifyCarJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Car $car;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($car)
    {
        $this->car = $car;
    }


    /**
     * @throws \Exception
     */
    public function handle()
    {
        $request = \App::make(Request1CService::class)->verifyCar($this->car);

        if($request){
            \App::make(UserCarService::class)->setNikoStatus($this->car, $request['car_status']);
            $userService = \App::make(UserService::class);
            $userService->setStatus($this->car->user, $request['account_status']);
            $userService->setLoyalty(
                $this->car->user,
                $request['loyalty_level'],
                $request['loyalty_type'],
                $request['level_up_amount'],
                $request['buy_cars']
            );
        }
    }
}
