<?php

namespace App\Services\Users;

use App\Models\Users\User;
use App\Models\Users\UserComment;
use App\Services\Events\EventService;
use App\Services\Events\User\UserEventService;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use DB;

class UserCommentService
{
    private ?User $user;

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function create(User $user, array $attributes, ?string $timezone = null): UserComment
    {
        try {
            $event = EventService::users($user)
                ->setLoggedUser($this->user);

            $comment = $user->comments()->create(
                $attributes + [
                    'author_id' => $this->user->id,
                    'timezone' => $timezone,
                ]
            );

            $event->update(UserEventService::ACTION_COMMENT_ADD);

            return $comment;

        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function destroy(User $user, UserComment $comment): UserComment
    {
        try {
            $event = EventService::users($user)
                ->setLoggedUser($this->user);

            $comment->delete();

            $event->update(UserEventService::ACTION_COMMENT_DELETE);

            return $comment;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    public function getComments(User $user): Collection
    {
        return $user->comments()
            ->with('author.roles')
            ->orderBy('id')
            ->get();
    }
}
