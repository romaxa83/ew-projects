<?php

namespace App\Events;

use App\Models\{Division, Order};
use App\Models\Zadarma\CallsEvents;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Traits\ResponseFormatter;
use App\Http\Controllers\CommunicationsController;

class ZadarmaCallEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels, ResponseFormatter;

    private $eventData;
    private $divisionID;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(CallsEvents $CallEvent, $divisionID)
    {
        $this->divisionID = $divisionID;
        $divisionData = Division::findOrFail($divisionID);
        $this->eventData = $this->getCommunicationPanelFormat($CallEvent, !empty($divisionData['miscs']['tz']) ? $divisionData['miscs']['tz'] : null);
        $this->eventData = CommunicationsController::mapRecord($this->eventData, true);

        //
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
        $channels = [new Channel('communications.' . $this->divisionID), new Channel('calls.' . $this->divisionID)];
        if ($this->eventData->client) {
            $Orders = Order::where('client_id', $this->eventData->client->id)->get(['id']);
            if ($Orders->isNotEmpty()) {
                foreach ($Orders as $Order) {
                    $channels[] = new Channel('order.' . $Order->id . '.communications');
                }
            }
        }
        return $channels;
    }
}
