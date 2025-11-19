<?php

namespace Wezom\Core\Dto;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Wezom\Core\Annotations\GraphQlType;
use Wezom\Core\Contracts\ParseGraphQlValue;
use Wezom\Core\GraphQL\Types\DateForFront;

class DateRangeDto extends Data implements ParseGraphQlValue
{
    public function __construct(
        #[GraphQlType(DateForFront::class)]
        public readonly ?Carbon $from,
        #[GraphQlType(DateForFront::class)]
        public readonly ?Carbon $to
    ) {
    }
}
