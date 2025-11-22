<?php

namespace App\Dto\Catalog\Troubleshoots;

use App\Models\Catalog\Troubleshoots\Troubleshoot;
use Illuminate\Http\UploadedFile;

class TroubleshootDto
{
    private bool $active;
    private string $name;
    private int $groupId;
    private null|UploadedFile $pdf;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->active = $args['active'] ?? Troubleshoot::DEFAULT_ACTIVE;
        $self->name = $args['name'];
        $self->groupId = $args['group_id'];
        $self->pdf = $args['pdf'] ?? null;

        return $self;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function getGroupId(): int
    {
        return $this->groupId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPdf(): null|UploadedFile
    {
        return $this->pdf;
    }
}

