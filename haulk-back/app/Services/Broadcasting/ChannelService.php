<?php

namespace App\Services\Broadcasting;

use App\Broadcasting\Channels\AdminChannel;
use App\Broadcasting\Channels\Channel;
use App\Models\Admins\Admin;
use App\Models\Users\User;
use Illuminate\Support\Collection;

class ChannelService
{
    /**
     * @param User $user
     * @return Collection
     */
    public function getChannelsForUser(User $user): Collection
    {
        $channels = Collection::make();

        foreach ($this->getUserChannels() as $channelClass) {
            /** @var Channel $channel */
            $channel = new $channelClass();

            if ($channel->isAllowedForUser($user)) {
                $channels->push($channel);
            }
        }

        return $channels;
    }

    protected function getUserChannels(): array
    {
        return config('broadcasting.channels');
    }

    /**
     * @param User $user
     * @return Collection
     */
    public function getChannelsForAdmin(Admin $admin): Collection
    {
        $channels = Collection::make();

        foreach ($this->getAdminChannels() as $channelClass) {
            /** @var AdminChannel $channel */
            $channel = new $channelClass();

            if ($channel->isAllowedForAdmin($admin)) {
                $channels->push($channel);
            }
        }

        return $channels;
    }

    protected function getAdminChannels(): array
    {
        return config('broadcasting.admin_channels');
    }

}
