<?php

namespace App\Services\Fax\Drivers;

class PreparedFaxMessage
{
    private string $from;

    private string $to;

    private string $url;

    public function __construct(string $from, string $to, string $url)
    {
        $this->from = $from;
        $this->to = $to;
        $this->url = $url;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function getFileUrl(): string
    {
        return $this->url;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

}
