<?php

namespace App\Models\Chat;

use App\Models\BaseTranslation;

class ChatMenuTranslation extends BaseTranslation
{
    public const TABLE = 'chat_menu_translations';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'title',
        'row_id',
        'language'
    ];
}
