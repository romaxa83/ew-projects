<?php

namespace App\Foundations\Modules\Seo\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface HasSeo
{
    public function seo(): MorphOne;
}
