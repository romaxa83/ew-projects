<?php

namespace App\Dto;

abstract class BaseFilterDto
{

    protected array $args;

    protected int $page;

    protected int $perPage;

    protected string $orderBy;

    protected string $orderType;

    protected array $filter = [];

    /**
     * @param string $field
     * @param string|null $fieldName
     * @return BaseFilterDto
     */
    protected function setFilterField(string $field, ?string $fieldName = null): self
    {
        $value = data_get($this->args, $field);

        if (empty($value)) {
            return $this;
        }

        $this->filter[$fieldName ?: $field] = $value;

        return $this;
    }

    /**
     * @param string $field
     * @param string|null $fieldName
     * @return BaseFilterDto
     */
    protected function setBooleanFilter(string $field, ?string $fieldName = null): self
    {
        $value = data_get($this->args, $field);

        if ($value === null) {
            return $this;
        }

        $this->filter[$fieldName ?: $field] = !empty($value);

        return $this;
    }

    /**
     * @param int $defaultPerPage
     * @return BaseFilterDto
     */
    protected function setPagination(int $defaultPerPage = 12): self
    {
        $this->page = (int)data_get($this->args, 'page', 1);
        $this->perPage = (int)data_get($this->args, 'per_page', $defaultPerPage);

        return $this;
    }

    /**
     * @param string $default
     * @return $this
     */
    protected function setOrderBy(string $default): self
    {
        $this->orderBy = data_get($this->args, 'order_by', $default);

        return $this;
    }

    /**
     * @param string $default
     * @return $this
     */
    protected function setOrderType(string $default = 'asc'): self
    {
        $this->orderType = data_get($this->args, 'order_type', $default);

        return $this;
    }

    /**
     * @return array
     */
    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return string
     */
    public function getOrderBy(): string
    {
        return $this->orderBy;
    }

    /**
     * @return string
     */
    public function getOrderType(): string
    {
        return $this->orderType;
    }

    abstract public static function create(array $args): self;
}
