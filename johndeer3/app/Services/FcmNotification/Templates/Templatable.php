<?php

namespace App\Services\FcmNotification\Templates;

interface Templatable
{
    public function getTitle(): string;
    public function getText(): string;
    public function getType(): string;
}
