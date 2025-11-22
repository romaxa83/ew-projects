<?php

namespace App\Events\User;

use App\Models\User\Car;
use Illuminate\Queue\SerializesModels;

class SendCarDataToAA
{
    use SerializesModels;

    public function __construct(
        public Car $car,
    )
    {}
}
