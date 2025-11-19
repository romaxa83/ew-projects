<?php

declare(strict_types=1);

namespace Wezom\Core\GraphQL\Types;

use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use Nuwave\Lighthouse\Schema\Types\Scalars\DateScalar;

class DateTimeForFront extends DateScalar
{
    protected string $format;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->format = config('app.datetime_format');
        $this->description = $this->description
            ?? sprintf('A datetime string with format `%s`', $this->format);
    }

    protected function format(Carbon $carbon): string
    {
        return $carbon->format($this->format);
    }

    protected function parse(mixed $value): Carbon
    {
        try {
            return Carbon::createFromFormat($this->format, $value);
        } catch (InvalidFormatException) {
            throw new InvalidArgumentException("Invalid date time format provided $value. Expected: $this->format");
        }
    }
}
