<?php

namespace WezomCms\Translates\DTO;

class TranslatesDto
{
    private array $translates;

    public function __construct(array $translates)
    {
        $this->translates = $translates;
    }

    public function toArray()
    {
        $data = [];
        foreach($this->translates ?? [] as $translate){
            $data[$translate['key']][$translate['locale']] = $translate['text'];
        }

        return $data;
    }
}
