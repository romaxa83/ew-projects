<?php

namespace Tests\Builders\Sips;

use App\Models\Sips\Sip;
use Tests\Builders\BaseBuilder;

class SipBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Sip::class;
    }
}
