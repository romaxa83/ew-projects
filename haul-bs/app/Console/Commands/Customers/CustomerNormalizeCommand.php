<?php

namespace App\Console\Commands\Customers;

use App\Models\Customers\Customer;
use Illuminate\Console\Command;
use Throwable;
class CustomerNormalizeCommand extends Command
{
    protected $signature = 'customers:normalize';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        try {
            $this->duplicate();

            $this->checkDuplicate();

        } catch (\Throwable $e) {
            dd($e);
        }

        return self::SUCCESS;
    }

    private function checkDuplicate(): void
    {
        $duplicates = Customer::select('email', \DB::raw('COUNT(id) as count'))
            ->groupBy('email')
            ->havingRaw('COUNT(id) > 1')
            ->get();

        if($duplicates->isNotEmpty()){
            $count = $duplicates->count();
            $this->error("Duplicates [$count]");
        } else{
            $this->info("No duplicates");
        }
    }

    private function duplicate(): void
    {
        $duplicates = Customer::select('email', \DB::raw('COUNT(id) as count'))
            ->groupBy('email')
            ->havingRaw('COUNT(id) > 1')
            ->get();

//        dd($duplicates);

        make_transaction(function() use ($duplicates) {
            foreach($duplicates as $duplicate) {

                if($duplicate->email == 'azamat.sarakaev.87@gmail.com' || $duplicate->email == 'lvladivanchenkol@gmail.com'){
                    $users = Customer::where('email', $duplicate->email)->get();

                    $user_1 = $users[0];
                    $user_2 = $users[1];

                    foreach($user_2->trucks as $truck) {
                        $truck->customer_id = $user_1->id;
                        $truck->save();
                    }
                    foreach($user_2->trailers as $trailer) {
                        $trailer->customer_id = $user_1->id;
                        $trailer->save();
                    }
                    foreach($user_2->partOrders as $order) {
                        $order->customer_id = $user_1->id;
                        $order->save();
                    }

                    $msg = "User haulk: [id:{$user_2->id}, original_id:{$user_2->origin_id}, email: {$user_2->email}] deleted";
                    $this->info($msg);
                    logger_info($msg);
                    $user_2->delete();
                }

                $users = Customer::where('email', $duplicate->email)->get();

                if(
                    str_starts_with($users[0]->first_name, 'AT')
                    && str_starts_with($users[1]->first_name, 'AT')
                ){
                    $msg_1 = "User TEST: [id:{$users[0]->id},firstName:{$users[0]->first_name} ] has orders";
                    $msg_2 = "User TEST: [id:{$users[1]->id},firstName:{$users[1]->first_name} ] has orders";

                    $this->info($msg_1);
                    logger_info($msg_1);

                    $this->info($msg_2);
                    logger_info($msg_2);

                    $users[0]->delete();
                    $users[1]->delete();
                    continue;
                }

                /** @var $userHaulk Customer */
                $userHaulk = $users->where('from_haulk', true)->first();
                $userBs = $users->where('from_haulk', false)->first();

                if($userHaulk && $userBs && $userBs->id != $userHaulk->id){

                    foreach($userHaulk->trucks as $truck) {
                        $truck->customer_id = $userBs->id;
                        $truck->save();
                    }
                    foreach($userHaulk->trailers as $trailer) {
                        $trailer->customer_id = $userBs->id;
                        $trailer->save();
                    }
                    foreach($userHaulk->partOrders as $order) {
                        $order->customer_id = $userBs->id;
                        $order->save();
                    }

                    $msg = "User haulk: [id:{$userHaulk->id},original_id:{$userHaulk->origin_id}, email:{$userHaulk->email} ] deleted";
                    $this->info($msg);
                    logger_info($msg);
                    $userHaulk->delete();
                }
            }
        });
    }
}
