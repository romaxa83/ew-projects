<?php

namespace App\Dto\Contacts;

use App\Dto\BaseDto;
use App\Http\Controllers\Api\Helpers\DateTimeHelper;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property-read Carbon $from
 * @property-read Carbon $to
 * @property-read bool $dayoff
 */
class WorkingHourDto extends BaseDto
{
    public const DAYS = [
        'mon',
        'tue',
        'wed',
        'thu',
        'fri',
        'sat',
        'sun'
    ];

    protected Carbon $from;
    protected Carbon $to;
    protected bool $dayoff;

    public static function makeCollection(array $workingHours): Collection
    {
        $result = collect();
        foreach (self::DAYS as $day) {
            if (empty($workingHours[$day])) {
                $result->put($day, null);
                continue;
            }
            $result->put($day, self::init($workingHours[$day]));
        }
        return $result;
    }

    public static function init(array $args): self
    {
        $dto = new self();

        $dto->from = DateTimeHelper::fromTime($args['from']);
        $dto->to = DateTimeHelper::fromTime($args['to']);
        $dto->dayoff = !empty($args['dayoff']);

        return $dto;
    }
}
