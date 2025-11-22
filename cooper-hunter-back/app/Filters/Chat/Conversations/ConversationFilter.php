<?php

namespace App\Filters\Chat\Conversations;

use App\Models\Chat\Conversation;
use App\Models\Technicians\Technician;
use Core\Chat\Models\Participation;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Conversation
 */
class ConversationFilter extends \Core\Chat\Filters\Conversations\ConversationFilter
{
    /**
     * Search by technicians among all chats
     */
    public function search(string $search): void
    {
        $this->whereHas('participants', static fn(Builder|Participation $p) => $p
            ->whereHasMorph(
                'messageable',
                [Technician::class],
                static fn(Builder|Technician $t) => $t
                    ->filter(['query' => $search])
            )
        );
    }
}
