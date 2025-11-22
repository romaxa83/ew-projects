<?php

namespace App\Listeners\User;

use App\Events\User\SaveCarFromAA;
use App\Models\User\Loyalty\Loyalty;
use App\Repositories\User\LoyaltyRepository;
use App\Services\Telegram\TelegramDev;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class AttachLoyaltyListeners
{
    public function __construct(protected LoyaltyRepository $loyaltyRepository)
    {}

    public function handle(SaveCarFromAA $event)
    {
        try {
            if($event->car->brand->isMain()){
                // привязка купонов к году продажи авто
                if($event->car->year_deal){
                    $year = Carbon::createFromDate($event->car->year_deal);
                    $now = CarbonImmutable::now();
                    $diff = $year->diffInYears($now);

                    $collection = collect();
                    $collection->push($this->loyaltyRepository->getItemForType($event->car, $diff, Loyalty::TYPE_SERVICE));
                    $collection->push($this->loyaltyRepository->getItemForType($event->car, $diff, Loyalty::TYPE_SPARES));
                    $collection->push($this->loyaltyRepository->getItemForTypeWithoutAge($event->car,  Loyalty::TYPE_BYU));

                    foreach ($collection as $item){
                        if($item){
//                            TelegramDev::info("Привязка купон [{$item->id}]", $event->user->name);
                            $event->user->loyalties()->attach($item->id, ['car_id' => $event->car->id]);
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }
}
