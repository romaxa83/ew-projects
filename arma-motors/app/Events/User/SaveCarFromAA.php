<?php

namespace App\Events\User;

use App\Models\User\Car;
use App\Models\User\User;
use Illuminate\Queue\SerializesModels;

class SaveCarFromAA
{
    use SerializesModels;

    public function __construct(
        public User $user,
        public Car $car
    )
    {}
}
