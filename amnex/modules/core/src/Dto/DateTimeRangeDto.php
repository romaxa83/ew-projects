<?php

namespace Wezom\Core\Dto;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Wezom\Core\Annotations\GraphQlType;
use Wezom\Core\Contracts\ParseGraphQlValue;
use Wezom\Core\GraphQL\Types\DateTimeForFront;

class DateTimeRangeDto extends Data implements ParseGraphQlValue
{
    public function __construct(
        #[GraphQlType(DateTimeForFront::class)]
        public readonly ?Carbon $from,
        #[GraphQlType(DateTimeForFront::class)]
        public readonly ?Carbon $to
    ) {
    }
}
