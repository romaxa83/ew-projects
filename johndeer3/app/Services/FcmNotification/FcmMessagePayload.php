<?php

namespace App\Services\FcmNotification;

use App\Services\FcmNotification\Templates\Templatable;

class FcmMessagePayload implements Templatable
{
    private $title;
    private $text;
    private $type;

    public function __construct($title, $text, $type)
    {
        $this->title = $title;
        $this->text = $text;
        $this->type = $type;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
