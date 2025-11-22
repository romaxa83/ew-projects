<?php

namespace App\Repositories\Orders\Dealer;

use App\Models\Dealers\Dealer;
use App\Models\Orders\Dealer\Order;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Builder;

final class OrderRepository extends AbstractRepository
{
    public function modelQuery(): Builder
    {
        return Order::query();
    }

    /**
     * @param int $id
     * @param array $dealersID
     * @return Order|null
     * @throws \Exception
     */
    public function getOneAccessibleToDealer(int $id, array $dealersID): ?Order
    {
        $model = $this->modelQuery()
            ->where('id', $id)
            ->whereIn('dealer_id', $dealersID)
            ->first();

        if(!$model){
            throw new \Exception('Not found', 404);
        }

        return $model;
    }

    public function checkUniqPO(Dealer $dealer, string $po, ?int $excludeID = null): bool
    {
        $dealerIds = $dealer->company->dealers->pluck('id')->toArray();

        $query = Order::query();

        if($excludeID){
            $query->where('id', '!=', $excludeID);
        }

        return $query
            ->whereIn('dealer_id', $dealerIds)
            ->where('po', $po)
            ->exists();
    }
}
