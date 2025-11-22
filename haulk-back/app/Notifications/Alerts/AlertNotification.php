<?php


namespace App\Notifications\Alerts;


use App\Channels\AlertsChannel;
use App\Models\Alerts\Alert;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AlertNotification extends Notification
{
    use Queueable;

    public const TARGET_TYPE_NEWS = 'news';
    public const TARGET_TYPE_ORDER = 'order';
    public const TARGET_TYPE_ORDER_COMMENT = 'order_comment';

    private int $companyId;
    private string $message;
    private ?string $type;
    private ?array $meta;
    private ?array $placeholders;

    public function __construct(int $companyId, string $message, ?string $type = null, ?array $meta = null, ?array $placeholders = null)
    {
        $this->companyId = $companyId;
        $this->message = $message;
        $this->type = $type;
        $this->meta = $meta;
        $this->placeholders = $placeholders;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable): array
    {
        return [AlertsChannel::class];
    }

    /**
     * @param mixed $notifiable
     * @return Alert
     */
    public function toAlert($notifiable): Alert
    {
        return new Alert(
            [
                'message' => $this->message,
                'type' => $this->type,
                'meta' => $this->meta,
                'placeholders' => $this->placeholders,
            ]
        );
    }

    public function getCompanyId(): int
    {
        return $this->companyId;
    }
}
