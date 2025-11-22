<?php

namespace App\Entities\Dictionaries;

use Carbon\Carbon;

class DictionaryItem
{
    public function __construct(
        public string $name,
        public int $count,
        public ?string $updated_at
    ) {
    }

    public function getUpdatedAt(): ?int
    {
        return $this->updated_at ? Carbon::make($this->updated_at)->getTimestamp() : null;
    }
}
