<?php

namespace Core\Chat\Enums;

use Core\Enums\BaseEnum;

/**
 * @method static static CONVERSATION_STARTED()
 * @method static static CONVERSATION_PROCESSED()
 * @method static static RECIPIENT_JOINED()
 * @method static static RECIPIENT_LEAVE()
 * @method static static MESSAGE_SENT()
 */
class ConversationUpdatedEventTypeEnum extends BaseEnum
{
    public const CONVERSATION_STARTED = 'conversation_started';

    public const CONVERSATION_PROCESSED = 'conversation_processed';

    public const RECIPIENT_JOINED = 'recipient_joined';
    public const RECIPIENT_LEAVED = 'recipient_leaved';

    public const MESSAGE_SENT = 'message_sent';
}
