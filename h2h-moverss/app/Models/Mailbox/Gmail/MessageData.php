<?php

namespace App\Models\Mailbox\Gmail;

use App\Helpers\DbConnections;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\Mailbox\Gmail\MessageData
 *
 * @property int message_id
 * @property string text
 * @method static Builder|MessageData newModelQuery()
 * @method static Builder|MessageData newQuery()
 * @method static Builder|MessageData query()
 * @method static Builder|MessageData whereMessageId($value)
 * @method static Builder|MessageData whereText($value)
 * @property int $message_id
 * @property string $text
 * @mixin \Eloquent
 */
class MessageData extends Model
{
    protected $connection = DbConnections::MAILBOX;

    public const TABLE = 'gmail_messages_data';
    protected $table = self::TABLE;

    public $timestamps = false;

    protected $fillable = [
        'text'
    ];
}
