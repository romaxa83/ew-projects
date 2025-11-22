<?php


namespace App\Dto\Chat\Menu;


class ChatMenuTranslationDto
{
    private string $language;
    private string $title;

    public static function byArgs(array $args): self
    {
        $translation = new self();

        $translation->language = $args['language'];
        $translation->title = $args['title'];

        return $translation;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
