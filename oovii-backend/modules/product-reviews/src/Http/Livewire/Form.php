<?php

namespace WezomCms\ProductReviews\Http\Livewire;

use Auth;
use DB;
use Event;
use Livewire\Component;
use Notification;
use NotifyMessage;
use WezomCms\Catalog\Models\Product;
use WezomCms\Core\Foundation\Helpers;
use WezomCms\Core\Foundation\JsResponse;
use WezomCms\Core\Models\Administrator;
use WezomCms\Core\Services\CheckForSpam;
use WezomCms\Pages\Models\Page;
use WezomCms\Pages\PagesServiceProvider;
use WezomCms\ProductReviews\Enums\Ratings;
use WezomCms\ProductReviews\Models\ProductReview;
use WezomCms\ProductReviews\Notifications\ProductReviewNotification;

/**
 * Class Form
 * @package WezomCms\ProductReviews\Http\Livewire
 * @property-read Page|null $commentRulesPage
 * @property-read ProductReview|null $replyToReview
 */
class Form extends Component
{
    /**
     * @var int
     */
    public $productId;

    /**
     * @var int|null
     */
    public $answerTo;

    /**
     * @var int
     */
    public $rating = 5;

    /**
     * @var string|null
     */
    public $name;

    /**
     * @var string|null
     */
    public $email;

    /**
     * @var string|null
     */
    public $text;

    /**
     * @param $id
     * @param  int|null  $answerTo
     */
    public function mount($id, $answerTo = null)
    {
        Product::published()->findOrFail($id);

        $this->productId = $id;

        $this->answerTo = $answerTo;

        $user = optional(Auth::user());

        $this->name = $user->full_name;
        $this->email = $user->email;
    }

    public function render()
    {
        return view('cms-product-reviews::site.livewire.form', [
            'ratings' => Ratings::asSelectArray(),
            'ratingText' => Ratings::getDescription($this->rating),
            'replyToReview' => $this->replyToReview,
            'commentRulesPage' => $this->commentRulesPage,
        ]);
    }

    /**
     * Validate only updated field.
     *
     * @param $field
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updated($field)
    {
        $this->validateOnly($field, ...$this->rules());
    }

    /**
     * Form submit handler
     * @param  CheckForSpam  $checkForSpam
     */
    public function submit(CheckForSpam $checkForSpam)
    {
        if (!$checkForSpam->checkInComponent($this)) {
            return;
        }

        $this->validate(...$this->rules());

        try {
            DB::transaction(function () {
                /** @var ProductReview $productReview */
                $productReview = ProductReview::make([
                    'product_id' => $this->productId,
                    'name' => $this->name,
                    'email' => $this->email,
                    'text' => $this->text,
                    'rating' => $this->rating,
                ]);

                if ($this->replyToReview) {
                    $productReview->parent()->associate($this->replyToReview);
                }

                if ($user = Auth::user()) {
                    $productReview->user()->associate($user);
                }

                $productReview->save();

                Event::dispatch('created_product_review', $productReview);

                $administrators = Administrator::toNotifications('product-reviews.edit')->get();
                Notification::send($administrators, new ProductReviewNotification($productReview));

                $this->reset('text', 'rating');

                JsResponse::make()
                    ->modal('close')
                    ->notification(__('cms-product-reviews::site.Form successfully submitted!'))
                    ->emit($this);
            });
        } catch (\Throwable $e) {
            report($e);

            JsResponse::make()
                ->success(false)
                ->notification(
                    NotifyMessage::error(__('cms-product-reviews::site.Error creating request!'))->asToast()
                )
                ->emit($this);
        }
    }

    /**
     * @return Page|null
     */
    public function getCommentRulesPageProperty(): ?Page
    {
        return Helpers::providerLoaded(PagesServiceProvider::class)
            ? Page::published()->find(settings('product-reviews.site.comment_rules_page_id'))
            : null;
    }

    /**
     * @return ProductReview|null
     */
    public function getReplyToReviewProperty(): ?ProductReview
    {
        return $this->answerTo
            ? ProductReview::published()->whereProductId($this->productId)->find($this->answerTo)
            : null;
    }

    /**
     * @return array
     */
    protected function rules(): array
    {
        return [
            [
                'rating' => [$this->replyToReview ? 'nullable' : 'required', 'int', 'min:1', 'max:5'],
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'text' => 'required|string|max:65535',
            ],
            [],
            [
                'rating' => __('cms-product-reviews::site.Rating'),
                'name' => __('cms-product-reviews::site.Name'),
                'email' => __('cms-product-reviews::site.E-mail'),
                'text' => __('cms-product-reviews::site.Text'),
            ]
        ];
    }
}
