<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $page_id
 * @property string $lang
 * @property string $name
 * @property string $text
 */

class PageTranslation extends Model
{
    use HasFactory;

    public $timestamps = false;

    const TABLE = 'page_translations';
    protected $table = self::TABLE;
}
