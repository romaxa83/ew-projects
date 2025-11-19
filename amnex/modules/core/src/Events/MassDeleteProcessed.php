<?php

namespace Wezom\Core\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

class MassDeleteProcessed
{
    use Dispatchable;

    /**
     * @param  class-string<Model>  $modelClass
     * @param  array<string>  $ids
     */
    public function __construct(public string $modelClass, public array $ids)
    {
        //
    }
}
