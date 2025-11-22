<?php

namespace App\Dto\Catalog\Manuals;

use Illuminate\Http\UploadedFile;

class ManualDto
{
    private int $manualGroupId;
    private UploadedFile $pdf;

    public static function byArgs(array $args): self
    {
        $self = new self();

        $self->manualGroupId = $args['manual_group_id'];
        $self->pdf = $args['pdf'];

        return $self;
    }

    public function getManualGroupId(): int
    {
        return $this->manualGroupId;
    }

    public function getPdf(): UploadedFile
    {
        return $this->pdf;
    }
}
