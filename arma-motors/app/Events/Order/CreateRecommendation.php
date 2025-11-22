<?php

namespace App\Events\Order;

use App\Models\Recommendation\Recommendation;
use Illuminate\Queue\SerializesModels;

class CreateRecommendation
{
    use SerializesModels;

    public function __construct(
        public Recommendation $model,
    )
    {}
}
