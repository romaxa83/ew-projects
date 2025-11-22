<?php

namespace Tests\Builders\Catalog\Certificates;

use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Certificates\CertificateType;
use Illuminate\Support\Str;
use Tests\Builders\BaseBuilder;

class CertificateBuilder
{
    private $number;
    private $link;
    private $typeId;

    // Number
    public function getNumber()
    {
        if(null == $this->number){
            $this->setNumber(Str::random(10));
        }
        return $this->number;
    }
    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    // Link
    public function getLink()
    {
        if(null == $this->link){
            $this->setLink(Str::random(10));
        }
        return $this->link;
    }
    public function setLink(string $value): self
    {
        $this->link = $value;

        return $this;
    }

    public function setTypeId(string $value): self
    {
        $this->typeId = $value;

        return $this;
    }
    public function getTypeId(): int
    {
        return $this->typeId ?? $this->getTypeRandom()->id;
    }

    private function getTypeRandom(): CertificateType
    {
        return app(TypeBuilder::class)->create();
    }

    public function create()
    {
        $model = $this->save();

        $this->clear();

        return $model;
    }

    private function save()
    {
        $data = [
            'certificate_type_id' => $this->getTypeId(),
            'number' => $this->getNumber(),
            'link' => $this->getLink(),
        ];

        return Certificate::factory()->new($data)->create();
    }

    private function clear()
    {
        $this->typeId = null;
        $this->number = null;
        $this->link = null;
    }
}



