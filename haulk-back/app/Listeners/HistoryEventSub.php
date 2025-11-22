<?php


namespace App\Listeners;


use Adideas\RelationFinder\Relations;
use App\Events\ModelChanged;
use App\Http\Resources\Orders\OrderResource;
use App\Models\History\History;
use App\Observers\HistoryObserver;
use Illuminate\Events\Dispatcher;
use Illuminate\Http\Resources\Json\JsonResource;

class HistoryEventSub
{
    /**
     * Handle the event.
     *
     * @param ModelChanged $event
     * @return void
     */
    public function onModelChanged($event)
    {
        if (!HistoryObserver::filter(null)) {
            return;
        }

        $event->model->morphMany(History::class, 'model')->create(
            [
                'message' => $event->message,
                'meta' => $event->meta,
                'histories' => $event->handler ? $event->handler->start() : [],
                'type' => $event->handler ? History::TYPE_CHANGES : History::TYPE_ACTIVITY,
                'user_id' => HistoryObserver::getUserID(),
                'user_role' => HistoryObserver::getUserRole(),
                'performed_at' => $event->performed_at ?? time(),
                'performed_timezone' => $event->performed_timezone ?? HistoryObserver::getUserCompanyTimezone(),
            ]
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param Dispatcher $events
     */
    public function subscribe($events)
    {
        $events->listen(
            ModelChanged::class,
            static::class . '@onModelChanged'
        );
    }

    public static function isIgnored($model, $key)
    {
        $blacklist = config('history.attributes_blacklist');
        $name = get_class($model);
        $array = $blacklist[$name] ?? null;
        return !empty($array) && in_array($key, $array);
    }

}
