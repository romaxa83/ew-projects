<?php

namespace App\Rules;

use App\Models\JD\ModelDescription;
use App\Repositories\JD\ModelDescriptionRepository;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;

class ModelDescriptionRule implements Rule, DataAwareRule
{
    protected array $data = [];
    protected $mdID;
    protected $egID;

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function passes($attribute, $value): bool
    {
        /** @var $md ModelDescription */
        $md = app(ModelDescriptionRepository::class)->getBy('id', $value);

        if($md){
            $this->mdID = $value;

            $tmp = explode('.', $attribute);
            $tmp[count($tmp) - 1] = 'equipment_group_id';
            $tmp = implode('.', $tmp);

            $egID = data_get($this->data, $tmp);
            $this->egID = $egID;

            return $egID == $md->equipmentGroup->id;
        }

        return true;
    }

    public function message(): string
    {
        return "This Equipment group [{$this->egID}] does not contain this Model Description [{$this->mdID}]";
    }
}

