<?php


namespace App\Dto\Clients;


class ClientBanDto
{
    private ?string $reason;
    private ?bool $showInInspection;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        if (empty($args['reason'])) {
            $dto->reason = null;
            $dto->showInInspection = null;
            return $dto;
        }
        $dto->reason = $args['reason'];
        $dto->showInInspection = (bool)$args['show_in_inspection'];

        return $dto;
    }

    /**
     * @return string|null
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }

    /**
     * @return bool|null
     */
    public function getShowInInspection(): ?bool
    {
        return $this->showInInspection;
    }
}
