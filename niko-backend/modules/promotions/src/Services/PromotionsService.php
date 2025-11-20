<?php

namespace WezomCms\Promotions\Services;

use WezomCms\Promotions\Http\Requests\Api\SetUsersFrom1CRequest;
use WezomCms\Promotions\Models\Promotions;
use WezomCms\TelegramBot\Jobs\TelegramSendJob;

class PromotionsService
{
    public function setUsersToPromotionsFrom1c(Promotions $promotion, SetUsersFrom1CRequest $request)
    {
        \DB::table('promotions_user_relation')
            ->where('promotions_id', $promotion->id)
            ->whereIn('user_id', $request['ActionClients'])
            ->delete();

        $data = [];
        foreach ($request['ActionClients'] as $key => $userId){
            $data[$key]['promotions_id'] = $promotion->id;
            $data[$key]['user_id'] = $userId;
        }

        \DB::table('promotions_user_relation')->insert($data);
    }
}
