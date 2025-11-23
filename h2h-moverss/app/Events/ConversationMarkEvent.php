<?php

namespace App\Events;

use App\Http\Controllers\CommunicationsController;
use App\Models\Communications\ConversationMark;
use App\Models\Order;
use App\Traits\ResponseFormatter;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationMarkEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, ResponseFormatter;

    private $eventData;
    private $divisionID;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ConversationMark $ConversationMark, $divisionID)
    {
        $this->divisionID = $divisionID;
        $this->eventData = $this->getCommunicationPanelFormat($ConversationMark);
        $this->eventData = CommunicationsController::mapRecord($this->eventData, true);
//        dump('Event Data');
//        dd($this->eventData);

    }

    public function broadcastAs()
    {
        return 'communications.event';
    }

    public function broadcastWith()
    {
        return ['data' => $this->eventData];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
//        return new PrivateChannel('communications');
        $channels = [new Channel('communications.' . $this->divisionID)];
        if ($this->eventData->collectionClients) {
            foreach ($this->eventData->collectionClients as $Client) {
                $Orders = Order::where('client_id', $Client->id)->get(['id']);
                if ($Orders->isNotEmpty()) {
                    foreach ($Orders as $Order) {
                        $channels[] = new Channel('order.' . $Order->id . '.communications');
                    }
                }
            }
        }
        return $channels;
    }
}
