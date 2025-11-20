<?php

namespace App\PAMI\Message\Event\Factory\Impl;

use App\PAMI\Message\Event\EventMessage;
use App\PAMI\Message\Event\UnknownEvent;
use App\PAMI\Message\Message;

/**
 * This factory knows which event to return according to a given raw message
 * from ami.
 */
class EventFactoryImpl
{
    public function __construct()
    {}

    /**
     * This is our factory method.
     *
     * @param string $message Literall message as received from ami.
     *
     * @return EventMessage
     */
    public static function createFromRaw($message)
    {
        $eventStart = strpos($message, 'Event: ') + 7;
        $eventEnd = strpos($message, Message::EOL, $eventStart);
        if ($eventEnd === false) {
            $eventEnd = strlen($message);
        }
        $name = substr($message, $eventStart, $eventEnd - $eventStart);
        $parts = explode('_', $name);
        $totalParts = count($parts);
        for ($i = 0; $i < $totalParts; $i++) {
            $parts[$i] = ucfirst($parts[$i]);
        }
        $name = implode('', $parts);
        $className = 'App\\PAMI\\Message\\Event\\' . $name . 'Event';
        if (class_exists($className)) {
            return new $className($message);
        }
        return new UnknownEvent($message);
    }
}
