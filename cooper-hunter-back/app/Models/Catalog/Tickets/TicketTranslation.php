<?php

namespace App\Models\Catalog\Tickets;

use App\Models\BaseTranslation;
use App\Traits\HasFactory;
use Database\Factories\Catalog\Tickets\TicketTranslationFactory;

/**
 * @property int id
 * @property string title
 * @property string description
 * @property int row_id
 * @property string language
 *
 * @method static TicketTranslationFactory factory(...$parameters)
 */
class TicketTranslation extends BaseTranslation
{
    use HasFactory;

    public const TABLE = 'ticket_translations';

    public $timestamps = false;

    protected $table = self::TABLE;

    protected $fillable = [
        'title',
        'description',
        'language',
    ];
}
