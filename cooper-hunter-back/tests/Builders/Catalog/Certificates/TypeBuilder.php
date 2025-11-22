<?php

namespace Tests\Builders\Catalog\Certificates;

use App\Models\Catalog\Certificates\CertificateType;
use Illuminate\Support\Str;
use Tests\Builders\BaseBuilder;

class TypeBuilder
{
    private $type;

    // Type
    public function getType()
    {
        if(null == $this->type){
            $this->setType(Str::random(10));
        }


        return $this->type;
    }
    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
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
            'type' => $this->getType(),
        ];

        return CertificateType::factory()->new($data)->create();
    }

    private function clear()
    {
        $this->type = null;
    }
}



