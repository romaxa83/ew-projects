<?php

namespace WezomCms\ProductReviews\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use WezomCms\ProductReviews\Models\ProductReview;

class ProductReviewNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var ProductReview
     */
    private $productReview;

    /**
     * Create a new notification instance.
     *
     * @param  ProductReview  $productReview
     */
    public function __construct(ProductReview $productReview)
    {
        $this->productReview = $productReview;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject(__('cms-product-reviews::admin.email.New product review'))
            ->markdown('cms-product-reviews::admin.notifications.email', [
                'productReview' => $this->productReview,
                'urlToAdmin' => route('admin.product-reviews.edit', $this->productReview->id),
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $productReview = $this->productReview;

        return [
            'route_name' => 'admin.product-reviews.edit',
            'route_params' => $productReview->id,
            'icon' => 'fa-comments',
            'color' => 'warning',
            'heading' => __('cms-product-reviews::admin.Product review'),
            'description' => sprintf(
                '%s %s (%s)',
                $productReview->name,
                $productReview->email,
                $productReview->product->name
            ),
        ];
    }
}
