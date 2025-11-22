<?php

namespace App\Http\Requests\GPS;

use App\Dto\GPS\GPSDataDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class GPSDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function getDto(): GPSDataDto
    {
        logger_flespi('[gps-stream] DATA 1: ', $this->all());

        return GPSDataDto::byParams($this->all());
    }

    public function getDtos(): Collection
    {
        logger_flespi('[gps-stream] DATA 2: ', $this->all());
        $collection = collect();
        foreach ($this->all() as $key => $data){
            $collection->put($key , GPSDataDto::byParams($data));
        }

        return $collection;
    }
}
