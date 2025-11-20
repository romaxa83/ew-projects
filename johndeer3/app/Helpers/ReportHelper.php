<?php

namespace App\Helpers;

class ReportHelper
{
    public static function prettyTitle($title)
    {
        return ucfirst(str_replace(' ', '_', strtolower($title)));
    }

    public static function titleForPdf($title)
    {
        return str_replace('\\', '-', str_replace('/', '-', $title));
    }
}
