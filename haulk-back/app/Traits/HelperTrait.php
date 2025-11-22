<?php


namespace App\Traits;


use Illuminate\Database\Eloquent\Model;

trait HelperTrait
{
    public function toggleStatus()
    {
        try {
            if (array_key_exists('status', $this->getAttributes())) {
                $this->status == true ? $this->status = false : $this->status = true;
                $this->save();
                return true;
            }
            return false;
        } catch (\Throwable $exception) {
            return false;
        }
    }

    /**
     * @param $params
     * @return $this
     */
    public function createFillableRow($params)
    {
        $this->fill($params);
        $this->save();
        return $this;
    }

    /**
     * @param $params
     * @return $this
     */
    public function updateFillableRow($params)
    {
        $this->fill($params);
        $this->update();
        return $this;
    }
}