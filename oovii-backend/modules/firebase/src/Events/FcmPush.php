<?php

namespace WezomCms\Firebase\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;
use WezomCms\Users\Models\User;

class FcmPush implements PushEvent
{
    use SerializesModels;

    private User $user;
    private null|Model $model;
    private string $type;

    public function __construct(User $user, $type, Model $relatedModel = null)
    {
        $this->user = $user;
        $this->type = $type;
        $this->model = $relatedModel;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getModel(): null|Model
    {
        return $this->model;
    }
}
