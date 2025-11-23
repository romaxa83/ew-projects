<?php

namespace WezomCms\Core\Traits;

trait ApiOrderByData
{
    protected string $asc = 'asc';
    protected string $desc = 'desc';

    protected string $defaultOrderBy = 'id';
    protected string $defaultOrderByType = 'desc';

    protected array $orderBy = [];
    protected array $orderByType = [];
    protected array $orderBySupport = [];

    public function orderDataForQuery(): array
    {
        $temp = [];
        foreach ($this->orderBy as $key => $item) {
            $temp[$item] = $this->orderByType[$key] ?? $this->defaultOrderByType;
        }

        return $temp;
    }

    private function checkAndFillOrderByType(string|array $values): void
    {
        if(is_array($values)){
            foreach ($values as $value){
                $this->fillOrderByType($value);
            }
            return;
        }

        $this->fillOrderByType($values);
    }

    private function fillOrderByType($value): void
    {
        if($this->checkSupportValue($value, [$this->asc, $this->desc])){
            $this->orderByType[] = $value;
        } else {
            $this->orderByType[] = $this->defaultOrderByType;
        }
    }

    private function checkAndFillOrderBy(string|array $values): void
    {
        if(is_array($values)){
            foreach ($values as $value){
                $this->fillOrderBy($value);
            }
            return;
        }

        $this->fillOrderBy($values);
    }

    public function fillOrderBy($value): void
    {
        if($this->checkSupportValue($value, $this->orderBySupport)){
            $this->orderBy[] = $value;
        }
    }

    public function checkSupportValue($value, $support): bool
    {
        return in_array(strtolower($value), $support);
    }
}

