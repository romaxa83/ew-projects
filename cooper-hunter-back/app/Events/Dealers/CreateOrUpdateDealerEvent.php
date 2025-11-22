<?php

namespace App\Events\Dealers;

use App\Dto\Dealers\DealerDto;
use App\Models\Dealers\Dealer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateOrUpdateDealerEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        protected Dealer $model,
        protected ?DealerDto $dto = null,
    )
    {}

    public function getDealer(): Dealer
    {
        return $this->model;
    }

    public function getDealerDto(): ?DealerDto
    {
        return $this->dto;
    }
}
