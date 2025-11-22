<?php


namespace App\Dto\Alerts;


use App\Enums\Users\UserMorphEnum;

class AlertRecipientDto
{
    private int $id;
    private UserMorphEnum $type;

    public static function byArgs(array $args): self
    {
        $dto = new self();
        $dto->id = $args['id'];
        $dto->type = UserMorphEnum::fromValue($args['type']);

        return $dto;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return UserMorphEnum
     */
    public function getType(): UserMorphEnum
    {
        return $this->type;
    }
}
