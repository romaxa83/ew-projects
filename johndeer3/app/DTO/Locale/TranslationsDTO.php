<?php

namespace App\DTO\Locale;

use App\Models\Translate;

class TranslationsDTO
{
    private $dtos = [];

    private function __construct()
    {}

    public static function byRequestFromApp(array $data): self
    {
        $self = new self();

        foreach ($data as $alias => $datum){
            foreach ($datum as $lang => $text){
                $self->dtos[] = TranslationDTO::byArgs([
                    "model" => Translate::TYPE_SITE,
                    "lang" => $lang,
                    "alias" => $alias,
                    "text" => $text,
                ]);
            }
        }

        return $self;
    }

    public function getDtos(): array
    {
        return $this->dtos;
    }
}


