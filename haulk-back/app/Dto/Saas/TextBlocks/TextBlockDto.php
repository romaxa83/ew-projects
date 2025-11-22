<?php


namespace App\Dto\Saas\TextBlocks;


use App\Dto\ArrayAccessDto;

class TextBlockDto extends ArrayAccessDto
{

    public string $group;

    public string $block;

    public array $scopes;

    public string $en;

    public ?string $es;

    public ?string $ru;

    public static function fromRequest(array $params): TextBlockDto
    {
        $dto = new self();

        $dto->group = data_get($params,'group');
        $dto->block = data_get($params,'block');
        $dto->scopes = data_get($params, 'scope', []);
        $dto->en = data_get($params,'en');
        $dto->es = data_get($params,'es');
        $dto->ru = data_get($params,'ru');
        return $dto;
    }

    public function getGroup(): string
    {
        return $this->group;
    }

    public function getBlock(): string
    {
        return mb_convert_case($this->block, MB_CASE_LOWER);
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function getEnText(): string
    {
        return $this->en;
    }

    public function getEsText(): ?string
    {
        return $this->es;
    }

    public function getRuText(): ?string
    {
        return $this->ru;
    }
}
