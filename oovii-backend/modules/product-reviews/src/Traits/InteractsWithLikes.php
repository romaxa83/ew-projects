<?php

namespace WezomCms\ProductReviews\Traits;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use WezomCms\ProductReviews\Models\ProductReview;

trait InteractsWithLikes
{
    private $likesKey = 'likes';
    private $dislikesKey = 'dislikes';
    private $sessionKey = 'reviews-voting';

    /**
     * @param $id
     */
    public function like($id)
    {
        $this->vote(ProductReview::published()->findOrFail($id), $this->likesKey);
    }

    /**
     * @param $id
     */
    public function dislike($id)
    {
        $this->vote(ProductReview::published()->findOrFail($id), $this->dislikesKey);
    }

    /**
     * @param  ProductReview  $review
     * @param  string  $field
     * @return bool
     */
    public function canVote(ProductReview $review, string $field): bool
    {
        return !$this->isMineReview($review) && !$this->alreadyVote($review, $field);
    }

    /**
     * @param  ProductReview|Builder  $review
     * @param  string  $field
     */
    protected function vote(ProductReview $review, string $field)
    {
        if ($this->canVote($review, $field) === false) {
            return;
        }

        $review->increment($field);

        // If the user has already given the opposite assessment,
        // then you need rollback to the previous value.
        $inverseAction = $field === $this->likesKey ? $this->dislikesKey : $this->likesKey;

        if ($this->alreadyVote($review, $inverseAction)) {
            if ($review->getAttribute($inverseAction) > 0) {
                $review->decrement($inverseAction);
            }

            $this->forgetVotingState($review, $inverseAction);
        }

        $this->saveVoting($review, $field);
    }

    /**
     * @param  ProductReview  $review
     * @return bool
     */
    protected function isMineReview(ProductReview $review): bool
    {
        return Auth::check() && Auth::user()->id === $review->user_id;
    }

    /**
     * @param  ProductReview  $review
     * @param  string  $action
     * @return bool
     */
    protected function alreadyVote(ProductReview $review, string $action): bool
    {
        $session = session()->get($this->sessionKey, []);
        if (in_array("{$action}-{$review->id}", $session)) {
            return true;
        }

        return false;
    }

    /**
     * @param  ProductReview  $review
     * @param  string  $action
     */
    protected function saveVoting(ProductReview $review, string $action)
    {
        $data = session()->get($this->sessionKey, []);

        $data[] = "{$action}-{$review->id}";

        session()->put($this->sessionKey, array_unique($data));
    }

    /**
     * @param  ProductReview  $review
     * @param  string  $action
     */
    protected function forgetVotingState(ProductReview $review, string $action)
    {
        $data = session()->get($this->sessionKey, []);

        $data = array_filter($data, function ($item) use ($action, $review) {
            return $item !== "{$action}-{$review->id}";
        });

        session()->put($this->sessionKey, array_unique($data));
    }
}
