<?php

namespace Tests\Fake\Http\Tickets;

class TicketFake
{
    public static function successCreate(): string
    {
        return <<<JSON
{
 "successful": true,
 "doc_number": "000004348",
 "guid": "1617e6bb-f07a-11ec-90f0-d05099fbe267",
 "error": "",
 "status": "Pending"
}
JSON;
    }
}