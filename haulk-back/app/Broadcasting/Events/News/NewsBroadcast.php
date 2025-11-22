<?php


namespace App\Broadcasting\Events\News;


use App\Broadcasting\Channels\NewsChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class NewsBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public int $id;

    protected int $companyId;

    public function __construct(int $newsId, int $companyId)
    {
        $this->id = $newsId;

        $this->companyId = $companyId;

        $this->dontBroadcastToCurrentUser();
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(NewsChannel::NAME . $this->companyId)
        ];
    }

    public function broadcastAs(): string
    {
        return $this->getName();
    }

    abstract protected function getName(): string;

}
