<?php

namespace WezomCms\Firebase\Receiver;

use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;
use WezomCms\Firebase\Models\Template;
use WezomCms\Users\Repositories\UserRepository;

class ReceiverManager
{
    protected $type;

    public function __construct($type)
    {
        $this->type = $type;
    }

    public function get(): Collection
    {
        return $this->handler();
    }

    private function handler(): Collection
    {
        $userRepo = app(UserRepository::class);

        return match ($this->type) {
            Template::TYPE_COLLECTION_START, Template::TYPE_COLLECTION_SOON_FINISH => $userRepo->getForCollectionNotification(),
            default => throw new InvalidArgumentException("Undefined template type [{$this->type}]"),
        };
    }
}

