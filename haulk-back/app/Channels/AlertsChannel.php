<?php


namespace App\Channels;


use App\Broadcasting\Events\Alerts\CompanyAlertBroadcast;
use App\Broadcasting\Events\Alerts\UserAlertBroadcast;
use App\Models\Users\User;
use App\Notifications\Alerts\AlertNotification;
use Exception;
use Log;

class AlertsChannel
{
    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param AlertNotification $notification
     * @return void
     */
    public function send($notifiable, AlertNotification $notification): void
    {
        try {
            $alert = $notification->toAlert($notifiable);

            if ($notifiable && $notifiable instanceof User) {
                $alert->carrier_id = $notifiable->getCompanyId();
                $alert->recipient_id = $notifiable->id;
                broadcast(new UserAlertBroadcast($notifiable->id, $notifiable->getCompanyId()));
            } else {
                $alert->carrier_id = $notification->getCompanyId();
                broadcast(new CompanyAlertBroadcast($notification->getCompanyId()));
            }

            $alert->save();
        } catch (Exception $e) {
            Log::error($e->getTraceAsString());
        }
    }
}
