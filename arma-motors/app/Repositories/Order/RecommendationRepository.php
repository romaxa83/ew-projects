<?php

namespace App\Repositories\Order;

use App\Models\Recommendation\Recommendation;
use App\Repositories\AbstractRepository;

class RecommendationRepository extends AbstractRepository
{
    public function query()
    {
        return Recommendation::query();
    }
}

