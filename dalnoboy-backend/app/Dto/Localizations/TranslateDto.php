<?php


namespace App\Dto\Localizations;


class TranslateDto
{
    private string $place;
    private string $key;
    private string $text;
    private string $lang;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->place = $args['place'];
        $dto->key = $args['key'];
        $dto->text = $args['text'];
        $dto->lang = $args['lang'];

        return $dto;
    }

    /**
     * @return string
     */
    public function getPlace(): string
    {
        return $this->place;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }
}
