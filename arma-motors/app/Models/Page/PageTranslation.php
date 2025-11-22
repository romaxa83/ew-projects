<?php

namespace App\Models\Page;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $page_id
 * @property string $lang
 * @property string|null $name
 * @property string|null $text
 * @property string|null $sub_text
 *
 */

class PageTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'page_translations';

    protected $table = self::TABLE;
}


