<?php

namespace App\Models\Files;

use Database\Factories\Files\FileFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @method static FileFactory factory(...$parameters)
 */
class File extends Media
{
    use HasFactory;
}
