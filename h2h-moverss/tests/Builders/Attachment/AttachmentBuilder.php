<?php

namespace Tests\Builders\Attachment;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Model;
use Tests\Builders\BaseBuilder;

class AttachmentBuilder extends BaseBuilder
{
    function modelClass(): string
    {
        return Attachment::class;
    }

    public function entity(Model $model): self
    {
        $this->data['entity_type'] = $model::MORPH_NAME;
        $this->data['entity_id'] = $model->id;

        return $this;
    }

    public function miscs(array $value): self
    {
        $this->data['miscs'] = $value;

        return $this;
    }
}
