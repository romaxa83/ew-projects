<?php

namespace App\Repositories\Promotion;

use App\Models\Promotion\Promotion;
use App\Models\User\User;
use App\Repositories\AbstractRepository;
use Illuminate\Database\Eloquent\Collection;

class PromotionRepository extends AbstractRepository
{
    public function query()
    {
        return Promotion::query();
    }

    public function getCommonAndIndividual(null|User $user): Collection
    {
        $query = $this->query()
            ->where('active', true)
            ->where('type', Promotion::TYPE_COMMON);

        if($user){
            $query->orWhereHas('users', function($q) use ($user){
                $q->where('user_id', $user->id);
            });
        }

        return $query->orderBy('type', 'desc')->get();
    }
}
