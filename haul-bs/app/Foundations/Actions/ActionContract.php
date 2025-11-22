<?php

namespace App\Foundations\Actions;

interface ActionContract
{
    public function exec(...$args): mixed;
}


