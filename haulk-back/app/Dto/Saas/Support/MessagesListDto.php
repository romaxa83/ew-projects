<?php


namespace App\Dto\Saas\Support;


use Illuminate\Support\Collection;

class MessagesListDto
{
    private Collection $messages;
    private int $total;

    public function __construct(Collection $messages, int $total)
    {
        $this->messages = $messages;
        $this->total = $total;
    }

    /**
     * @return Collection
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return bool
     */
    public function hasOlder(): bool
    {
        return $this->total > $this->messages->count();
    }
}
