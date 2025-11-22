<?php

namespace Tests\Builders\Commercial;

use App\Models\Commercial\CommercialProject;
use App\Models\Commercial\CommercialQuote;
use Tests\Builders\BaseBuilder;

class QuoteBuilder extends BaseBuilder
{
    protected function modelClass(): string
    {
        return CommercialQuote::class;
    }

    public function setShippingPrice(float $value): self
    {
        $this->data['shipping_price'] = $value;

        return $this;
    }

    public function setStatus($value): self
    {
        $this->data['status'] = $value;

        return $this;
    }

    public function setProject(CommercialProject $project): self
    {
        $this->data['commercial_project_id'] = $project->id;

        return $this;
    }

    public function setCreatedAt($value): self
    {
        $this->data['created_at'] = $value;

        return $this;
    }
}



