<?php

namespace App\Dto\Orders\Dealer;

use App\Dto\SimpleTranslationDto;

class PaymentDescDto
{
    public $type;

    /** @var array<SimpleTranslationDto> */
    public array $translations = [];

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->type = data_get($args, 'type');

        foreach (data_get($args, 'translations', []) as $item){
            $dto->translations[] = SimpleTranslationDto::byArgs($item);
        }

        return $dto;
    }
}
