<?php

namespace App\Models\Mailbox\Gmail;

use App\Helpers\DbConnections;
use App\Models\Client\Email;
use App\Models\Communications\CommunicationRecord;
use Carbon\CarbonImmutable;
use Database\Factories\Gmail\MessageFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * App\Models\Mailbox\Gmail\Message
 *
 * @property int id
 * @property int account_id     //ID аккаунта
 * @property string msg_id
 * @property string|null thread_id
 * @property string|null history_id
 * @property string tags
 * @property string tag        //Main Folder
 * @property string|null subject
 * @property mixed  miscs
 * @property CarbonImmutable created_at      //GTM
 * @property CarbonImmutable updated_at
 * @property-read \App\Models\Mailbox\Gmail\MessageData|null $data
 * @property-read \App\Models\Mailbox\Gmail\Account|null $account
 * @method static Builder|Message newModelQuery()
 * @method static Builder|Message newQuery()
 * @method static Builder|Message query()
 * @method static Builder|Message searchByEmails(array $emails)
 * @method static Builder|Message threadMsg($thread_id, $account_ids)
 * @method static Builder|Message whereAccountId($value)
 * @method static Builder|Message whereCreatedAt($value)
 * @method static Builder|Message whereHistoryId($value)
 * @method static Builder|Message whereId($value)
 * @method static Builder|Message whereMiscs($value)
 * @method static Builder|Message whereMsgId($value)
 * @method static Builder|Message whereSubject($value)
 * @method static Builder|Message whereTag($value)
 * @method static Builder|Message whereTags($value)
 * @method static Builder|Message whereThreadId($value)
 * @method static Builder|Message whereUpdatedAt($value)
 * @method static Builder|Message fromNotIn(array $emails)
 * @method static Builder|Message emailFromIn(array $emails)
 * @method static Builder|Message toNotIn(array $emails)
 * @method static Builder|Message emailToIn(array $emails)
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null audits_count
 * @method static Builder|Message searchByEmailLike($term)
 * @method static Builder|Message searchByClients(array $clientIDs)
 * @method static MessageFactory factory(...$parameters)
 * @mixin \Eloquent
 *
 * @see self::communicationRecord()
 * @property CommunicationRecord|MorphOne communicationRecord
 */
class Message extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;

    public const MORPH_NAME = 'gmail-messages';

    protected $connection = DbConnections::MAILBOX;

    public const TABLE = 'gmail_messages';
    protected $table = self::TABLE;

    public const TAG_SENT = 'sent';
    public const TAG_INBOX = 'inbox';

    public const TAG_TRASH = 'trash';

    protected $fillable = [
        'tags',
        'updated_at'
    ];

    protected $casts = [
        'miscs' => 'array',
    ];

    protected static function newFactory(): MessageFactory
    {
        return MessageFactory::new();
    }

    public function isInbound(): bool
    {
        return $this->tag === self::TAG_INBOX;
    }

    public function isOutbound(): bool
    {
        return $this->tag === self::TAG_SENT;
    }

    public function data(): HasOne
    {
        return $this->hasOne(MessageData::class, 'message_id', 'id');
    }

    public function account()
    {
        return $this->hasOne(Account::class, 'id', 'account_id');
    }

    public function communicationRecord(): MorphOne
    {
        return $this->morphOne(CommunicationRecord::class, 'entity');
    }

    public function scopeThreadMsg($q, $thread_id, $account_ids)
    {
        return $q->whereThreadId($thread_id)
            ->whereIn('account_id', $account_ids)
            ->with('data')
            ->orderBy('created_at');
    }

    public function scopeFromNotIn($q, array $emails)
    {
        return $q->where(function ($q) use ($emails) {
            return $q->whereNotIn('miscs->from->email', $emails);
        });
    }

    public function scopeToNotIn(Builder $q, array $emails)
    {
        return $q->where(function (Builder $q) use ($emails) {
            foreach ($emails as $email) {
                $q->whereJsonDoesntContain('miscs->to', ['email' => $email]);
            }
            return $q;
        });
    }

    public function scopeEmailFromIn($q, array $emails)
    {
        return $q->where(function ($q) use ($emails) {
            return $q->whereIn('miscs->from->email', $emails);
        });
    }

    public function scopeEmailToIn($q, array $emails)
    {
        return $q->where(function ($q) use ($emails) {
            return $q->whereIn('miscs->to->email', $emails);
        });
    }

    public function scopeSearchByClients(Builder $q, array $clientIDs) {
        $clientEmails = Email::whereIn('client_id', $clientIDs)->get(['id', 'value']);
        if ($clientEmails->isNotEmpty()) {
            $q->where(function (Builder $query) use ($clientEmails) {
                return $query->orWhere(function (Builder $q) use ($clientEmails) {
                    return $q->searchByEmails($clientEmails->pluck('value')->toArray());
                });
            });
        }
        return $q;
    }


    public function scopeSearchByEmailLike(Builder $q, $term)
    {
        return $q->where(function (Builder $q) use ($term) {
            return $q->where(function (Builder $q) use ($term) {
                $q->whereIn('tag', ['inbox', 'spam']);
                $q->where(DB::raw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(gmail_messages.miscs, \"$.from.email\")))"), 'LIKE', "%{$term}%");
            })->orWhere(function (Builder $q) use ($term) {
                $q->whereIn('tag', ['sent']);
                $q->whereRaw("JSON_SEARCH(LOWER(JSON_UNQUOTE(JSON_EXTRACT(gmail_messages.miscs, \"$.to\"))), 'one', ?, null, '$[*].email') IS NOT NULL", "%{$term}%");
            });
        });

    }

    public function scopeSearchByEmails(Builder $q, array $emails)
    {
        $query = $q->where(function (Builder $q) use ($emails) {
            $emails = array_map('strtolower', $emails);
            return $q->where(function (Builder $q) use ($emails) {
                $q->whereIn('tag', ['inbox', 'spam']);
                $q->where(function (Builder $q) use ($emails) {
                    foreach ($emails as $email) {
                        $q->orWhere(DB::raw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(gmail_messages.miscs, \"$.from.email\")))"), $email);
                    }
                });

            })->orWhere(function (Builder $q) use ($emails) {
                $q->whereIn('tag', ['sent']);
                $q->where(function (Builder $q) use ($emails) {
                    foreach ($emails as $email) {
                        $q->orWhereRaw("JSON_CONTAINS(LOWER(JSON_UNQUOTE(JSON_EXTRACT(gmail_messages.miscs, \"$.to\"))), ?)", '{"email":"' . $email . '"}');
                    }
                });
            });
        });
        return $query;
    }
}
