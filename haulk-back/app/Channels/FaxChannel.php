<?php

namespace App\Channels;

use App\Jobs\Fax\SendFaxJob;
use App\Notifications\Messages\FaxMessage;
use App\Notifications\Orders\SendDocs;
use App\Notifications\SendPdfBol;
use App\Notifications\SendPdfInvoice;
use App\Services\Fax\DefaultStatusHandler;
use App\Services\Fax\Drivers\FaxProcess;
use App\Services\Fax\Drivers\PreparedFaxMessage;
use App\Services\Fax\Handlers\StatusHandler;
use App\Services\Fax\StatusHandleable;
use Exception;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification;
use Storage;

class FaxChannel
{

    /**
     * @param AnonymousNotifiable $notifiable
     * @param Notification|SendDocs $notification
     * @throws Exception
     */
    public function send(AnonymousNotifiable $notifiable, Notification $notification)
    {
        if (!$fax = $notifiable->routes['fax'] ?? null) {
            return;
        }

        $faxMessage = $notification->toFax($notifiable);

        foreach ($faxMessage->getRawAttachments() as $attachment) {
            $fileUrl = $this->prepareAttachmentUrl($faxMessage, $attachment);

            $message = new PreparedFaxMessage($faxMessage->getFrom(), $fax, $fileUrl);

            $handler = $this->getStatusHandlerByFaxMessage($notification)
                ->setMessage($message)
                ->setFileName($attachment['name']);

            SendFaxJob::dispatch($message, $handler, new FaxProcess());
        }
    }

    protected function prepareAttachmentUrl(FaxMessage $faxMessage, array $attachment): string
    {
        $path = sprintf(
            '%s/faxes/orders/%s/%s',
            config('medialibrary.directory'),
            $faxMessage->getOrder()->getKey(),
            $attachment['name']
        );

        if (Storage::exists($path)) {
            Storage::delete($path);
        }

        Storage::put($path, $attachment['data']);

        return Storage::url($path);
    }

    protected function getStatusHandlerByFaxMessage($notification): StatusHandler
    {
        if ($notification instanceof StatusHandleable) {
            return $notification->getStatusHandler();
        }

        return new DefaultStatusHandler();
    }
}
