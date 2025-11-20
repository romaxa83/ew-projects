<?php

namespace WezomCms\Requests\Events;

use Illuminate\Queue\SerializesModels;

class VerifyCarRequest
{
    use SerializesModels;

    /**
     * @var string|int
     */
    public $car;

    public function __construct($car)
    {
        $this->car = $car;
    }
}
