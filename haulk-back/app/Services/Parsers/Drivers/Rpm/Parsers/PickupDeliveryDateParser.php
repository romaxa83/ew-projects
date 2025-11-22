<?php

namespace App\Services\Parsers\Drivers\Rpm\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PickupDeliveryDateParser extends ValueParserAbstract
{
    private const PATTERN_DATE = "/^(?<date>[A-Z][a-z]{2} [0-9]{1,2}, [0-9]{4})/s";
    private const PATTERN_TIME = "/^ {3,}(?<time>[0-9]{2}:[0-9]{2} - [0-9]{2}:[0-9]{2}) [A-Z]+/m";
    private string $text;

    private array $result = [
        'date' => null,
        'time_interval' => null
    ];

    public function parse(string $text): Collection
    {
        $this->text = trim($this->replacementIntend($this->replaceBefore($text)));
        return $this
            ->parseDate()
            ->parseTimeInterval()
            ->getCollection();
    }

    private function parseDate(): self
    {
        if (!preg_match(self::PATTERN_DATE, $this->text, $match)) {
            return $this;
        }
        $this->result['date'] = Carbon::createFromFormat("M j, Y", $match['date'])->format("m/d/Y");
        return $this;
    }

    private function parseTimeInterval(): self
    {
        if (!preg_match(self::PATTERN_TIME, $this->text, $match)){
            return $this;
        }
        $interval = explode(" - ", $match['time']);
        $this->result['time_interval'] = [
            'from' => Carbon::createFromFormat('H:i', $interval[0])->format('g:i A'),
            'to' => Carbon::createFromFormat('H:i', $interval[1])->format('g:i A'),
        ];
        return $this;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
