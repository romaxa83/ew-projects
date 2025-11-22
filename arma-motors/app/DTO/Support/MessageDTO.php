<?php

namespace App\DTO\Support;

use App\Traits\AssetData;
use App\ValueObjects\Email;

final class MessageDTO
{
    use AssetData;

    private int|string $categoryId;
    private null|int|string $userId = null;
    private Email $email;
    private null|string $text;

    private function __construct(){}

    public static function byArgs(array $args): self
    {
        self::assetFieldAll($args, 'categoryId');
        self::assetFieldAll($args, 'email');

        $self = new self();

        $self->categoryId = $args['categoryId'];
        $self->email = new Email($args['email']);
        $self->userId = $args['userId'] ?? null;
        $self->text = $args['text'] ?? null;

        return $self;
    }

    public function getCategoryId(): int|string
    {
        return $this->categoryId;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getUserId(): null|int|string
    {
        return $this->userId;
    }

    public function getText(): null|string
    {
        return $this->text;
    }
}
