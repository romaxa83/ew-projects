<?php

namespace App\Dto\Commercial;

use App\ValueObjects\Email;
use App\ValueObjects\Phone;

class CommercialCredentialsDto
{
    private string $companyName;
    private Phone $companyPhone;
    private Email $companyEmail;
    private int $projectId;
    private ?string $comment;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->companyName = $args['company_name'];
        $dto->companyPhone = new Phone($args['company_phone']);
        $dto->companyEmail = new Email($args['company_email']);
        $dto->projectId = $args['project_id'];
        $dto->comment = $args['comment'] ?? null;

        return $dto;
    }

    public function getCompanyName(): string
    {
        return $this->companyName;
    }

    public function getCompanyPhone(): Phone
    {
        return $this->companyPhone;
    }

    public function getCompanyEmail(): Email
    {
        return $this->companyEmail;
    }

    public function getProjectId(): int
    {
        return $this->projectId;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }
}