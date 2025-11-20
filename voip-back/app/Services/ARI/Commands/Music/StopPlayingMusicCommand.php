<?php

namespace App\Services\ARI\Commands\Music;

use App\Services\ARI\Commands\BaseCommand;
use App\Services\ARI\Exceptions\CommandException;

class StopPlayingMusicCommand extends BaseCommand
{
    protected ?string $channelId = null;

    public function channelId(string $channelId): self
    {
        $this->channelId = $channelId;
        return $this;
    }

    protected function deleteUri(): string
    {
        if(!$this->channelId){
            throw new CommandException("The [".__CLASS__."] requires a channelId");
        }

        return "ari/asterisk/channels/{$this->channelId}/moh";
    }
}
