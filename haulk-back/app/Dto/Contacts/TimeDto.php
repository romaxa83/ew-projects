<?php

namespace App\Dto\Contacts;

use App\Dto\BaseDto;
use App\Http\Controllers\Api\Helpers\DateTimeHelper;
use Illuminate\Support\Carbon;

/**
 * @property-read Carbon $from
 * @property-read Carbon $to
 */
class TimeDto extends BaseDto
{
    protected Carbon $from;
    protected Carbon $to;

    public static function init(array $args): self
    {
        $dto = new self();

        $dto->from = DateTimeHelper::fromTime($args['from']);
        $dto->to = DateTimeHelper::fromTime($args['to']);

        return $dto;
    }
}
