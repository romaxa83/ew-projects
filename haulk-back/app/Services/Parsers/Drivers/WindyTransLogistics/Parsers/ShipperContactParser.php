<?php

namespace App\Services\Parsers\Drivers\WindyTransLogistics\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class ShipperContactParser extends ValueParserAbstract
{
    private array $result = [
        'full_name' => null,
        'address' => null,
        'city' => null,
        'state' => null,
        'zip' => null,
        'phones' => null,
        'fax' => null
    ];

    public function parse(string $text): Collection
    {
        $contact = explode("\n", $this->replaceBefore($text));
        $this->result['full_name'] = $contact[0];
        $this->result = array_merge(
            $this->result,
            $this->parseLocation(
                "/^(?<address>[^,]+), (?<city>[^,]+), (?<state>[A-Z]{2}) (?<zip>[0-9]+)/",
                $contact[1],
                ['city', 'state', 'zip', 'address']
            )
        );
        $phone = preg_replace("/\D/", "", $contact[2]);
        if (!empty($phone)) {
            $this->result['phones'][] = [
                'number' => $phone
            ];
        }
        $fax = preg_replace("/\D/", "", $contact[3]);
        if (!empty($fax)) {
            $this->result['fax'] = $fax;
        }
        return collect($this->result);
    }
}
