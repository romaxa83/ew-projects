<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\DataAwareRule;

class OneFieldsRequired implements Rule, DataAwareRule
{
    protected $data = [];
    public $fields = [];

    public function __construct(...$fields)
    {
        $this->fields = $fields;
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function passes($attribute, $value): bool
    {
        foreach ($value ?? [] as $k => $item){
            $c = 0;
            foreach ($this->fields as $field) {
                if(array_key_exists($field, $item)){
                    $c++;
                }
            }
            if($c == 0){
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        $fields =  implode(', ', $this->fields);

        return __("One of these fields [{$fields}] must exist");
    }
}
