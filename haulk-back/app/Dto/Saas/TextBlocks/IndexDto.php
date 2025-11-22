<?php


namespace App\Dto\Saas\TextBlocks;


class IndexDto
{

    private int $perPage;

    private int $page;

    private array $params;

    private array $filter = [];

    public static function fromRequest(array $params): IndexDto
    {
        $dto = new self();

        $dto->params = $params;

        $dto->setFilterField('query')
            ->setFilterField('group_block', 'group')
            ->setFilterField('scope');

        $dto->perPage = (int)data_get($params,'per_page', config('admins.paginate.per_page'));

        $dto->page = (int)data_get($params, 'page', 1);
        return $dto;
    }

    private function setFilterField(string $field, ?string $fieldName = null): IndexDto
    {
        $value = data_get($this->params, $field);

        if (!empty($value)) {
            $this->filter[$fieldName !== null ? $fieldName : $field] = $value;
        }

        return $this;
    }

    public function getFilter(): array
    {
        return $this->filter;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }
}
