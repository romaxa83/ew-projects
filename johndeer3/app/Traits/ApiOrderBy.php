<?php

namespace App\Traits;

trait ApiOrderBy
{
    protected $asc = 'asc';
    protected $desc = 'desc';

    protected $defaultOrderBy = 'id';
    protected $defaultOrderByType = 'desc';

    protected $orderBy = [];
    protected $orderByType = [];
    protected $orderBySupport = [];

    public function orderDataForQuery(): array
    {
        $temp = [];
        foreach ($this->orderBy as $key => $item) {
            $temp[$item] = $this->orderByType[$key] ?? $this->defaultOrderByType;
        }

        return $temp;
    }

    public function checkAndFillOrderByType($values): void
    {
        if(is_array($values)){
            foreach ($values as $value){
                $this->fillOrderByType($value);
            }
            return;
        }

        $this->fillOrderByType($values);
    }

    public function fillOrderByType($value): void
    {
        if($this->checkSupportValue($value, [$this->asc, $this->desc])){
            $this->orderByType[] = $value;
        } else {
            $this->orderByType[] = $this->defaultOrderByType;
        }
    }

    public function checkAndFillOrderBy($values): void
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

    public function setOrderBySupport(...$value): void
    {
        $this->orderBySupport = $value;
    }
}
