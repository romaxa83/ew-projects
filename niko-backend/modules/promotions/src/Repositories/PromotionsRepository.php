<?php

namespace WezomCms\Promotions\Repositories;

use Illuminate\Database\Eloquent\Collection;
use WezomCms\Core\Repositories\AbstractRepository;
use WezomCms\Promotions\Models\Promotions;

class PromotionsRepository extends AbstractRepository
{
    protected function query()
    {
        return Promotions::query();
    }

    public function getAllWithIndividual($userId = null, $orderField = 'sort'): Collection
    {
        $commonPromotions = $this->getByType(Promotions::TYPE_COMMON, $orderField);

        if($userId){
            $individual = $this->query()
                ->published()
                ->with('users')
                ->whereHas('users', function($q) use ($userId){
                    $q->where('user_id', $userId);
                })->orderBy($orderField)->get();

            if($individual->isEmpty()){
                $individual = $this->query()
                    ->published()
                    ->where('type', Promotions::TYPE_INDIVIDUAL_FOR_APP)
                    ->orderBy($orderField)->get();
            }

            $commonPromotions = $commonPromotions->merge($individual);
        }

        return $commonPromotions;
    }

    public function getByType($type, $orderField = 'sort'): Collection
    {
        return $this->query()->published()->where('type', $type)->orderBy($orderField)->get();
    }

    public function getByCode($code)
    {
        return $this
            ->query()
            ->where('code_1c', $code)
            ->first();
    }
}
