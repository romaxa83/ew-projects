<?php

namespace App\Models;

interface Languageable
{
    public function getLangSlug(): string;
}
