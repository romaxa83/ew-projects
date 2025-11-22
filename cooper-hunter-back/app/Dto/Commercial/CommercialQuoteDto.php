<?php

namespace App\Dto\Commercial;

use App\Models\Commercial\CommercialQuote;
use App\ValueObjects\Email;
use Illuminate\Http\UploadedFile;

class CommercialQuoteDto
{
    private Email $email;
    private UploadedFile $file;
    private int $projectId;
    private string $status;
    private string $shippingAddress;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->email = new Email($args['email']);
        $dto->file = $args['file'];
        $dto->projectId = $args['project_id'];
        $dto->status = $args['status'] ?? CommercialQuote::DEFAULT_STATUS;
        $dto->shippingAddress = $args['shipping_address'];

        return $dto;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function getFile(): UploadedFile
    {
        return $this->file;
    }

    public function getProjectId(): int
    {
        return $this->projectId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getShippingAddress(): string
    {
        return $this->shippingAddress;
    }
}
