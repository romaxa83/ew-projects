<?php

namespace WezomCms\Requests\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use WezomCms\Requests\Services\Request1CService;
use WezomCms\ServicesOrders\Models\ServicesOrder;
use WezomCms\ServicesOrders\Services\OrderService;

class OrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ServicesOrder $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ServicesOrder $order)
    {
        $this->order = $order;
    }


    /**
     * @throws \Exception
     */
    public function handle()
    {
        $request = \App::make(Request1CService::class)->order($this->order);
        if($request){
            \App::make(OrderService::class)->setStatus($this->order, $request['status']);
        }
    }
}
