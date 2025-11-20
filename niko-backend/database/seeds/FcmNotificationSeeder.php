<?php

use WezomCms\Promotions\Models\Promotions;
use WezomCms\Promotions\Models\PromotionsTranslation;
use WezomCms\Users\Models\User;

class FcmNotificationSeeder extends BaseSeeder
{
    public function run()
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \DB::table('fcm_notifications')->truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        try {
            \DB::transaction(function (){

                $users = User::query()->get()->pluck('id')->toArray();

                foreach ($users as $user) {

                    for ($i = 1; $i != 7; $i++) {
                        $someOrderId = \WezomCms\ServicesOrders\Models\ServicesOrder::query()
                            ->where('user_id', $user)->orderBy(DB::raw('RAND()'))
                            ->take(1)->pluck('id')->toArray();

                        if($someOrderId){

                            for ($j = 1; $j != 4; $j++){
                                factory(\WezomCms\Firebase\Models\FcmNotification::class)->create([
                                    'user_id' => $user,
                                    'service_order_id' => $someOrderId[0],
                                    'type' => \WezomCms\Firebase\Types\FcmNotificationType::ORDER,
                                    'status' => \WezomCms\Firebase\Types\FcmNotificationStatus::SEND,
                                    'data' => [
                                        'title' => $this->getFaker()->realText($this->getFaker()->numberBetween(10, 15)),
                                        'body' => $this->getFaker()->realText($this->getFaker()->numberBetween(10, 30))
                                    ],
                                    'created_at' => \Carbon\Carbon::now()->subHours($j),
                                    'updated_at' => \Carbon\Carbon::now()->subHours($j)
                                ]);
                            }
                        }
                    }

                    for ($i = 1; $i != 8; $i++){
                        factory(\WezomCms\Firebase\Models\FcmNotification::class)->create([
                            'user_id' => $user,
                            'service_order_id' => null,
                            'type' => \WezomCms\Firebase\Types\FcmNotificationType::PROMOTION,
                            'status' => \WezomCms\Firebase\Types\FcmNotificationStatus::SEND,
                            'data' => [
                                'title' => $this->getFaker()->realText($this->getFaker()->numberBetween(10, 15)),
                                'body' => $this->getFaker()->realText($this->getFaker()->numberBetween(10, 30))
                            ],
                            'created_at' => \Carbon\Carbon::now()->subHours($j),
                            'updated_at' => \Carbon\Carbon::now()->subHours($j)
                        ]);
                    }
                }

            });
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    }
}
